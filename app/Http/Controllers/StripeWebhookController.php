<?php

namespace App\Http\Controllers;

use App\Mail\ReservationConfirmedAdminMail;
use App\Mail\ReservationConfirmedMail;
use App\Models\Reservation;
use App\Services\CuponService;
use App\Services\HotelConfig;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Resend\Laravel\Facades\Resend;
use Stripe\Exception\SignatureVerificationException;
use Stripe\StripeClient;
use Stripe\Webhook;
use UnexpectedValueException;

class StripeWebhookController extends Controller
{
public function handle(Request $request, ?string $hotel = null)
{
    $payload   = $request->getContent();
    $sigHeader = $request->header('Stripe-Signature');

    try {
        $hotelCode = HotelConfig::normalize($hotel);
        $secret = HotelConfig::stripeWebhookSecret($hotelCode);
        $stripeSecret = HotelConfig::stripeSecret($hotelCode);
    } catch (\InvalidArgumentException $e) {
        return response($e->getMessage(), 500);
    }

    if (!$secret) {
        return response('Falta STRIPE_WEBHOOK_SECRET', 500);
    }

    try {
        $event = Webhook::constructEvent($payload, $sigHeader, $secret);
    } catch (UnexpectedValueException $e) {
        return response('Invalid payload', 400);
    } catch (SignatureVerificationException $e) {
        return response('Invalid signature', 400);
    }

    // ACK default (Stripe no reintenta si respondemos 2xx)
    $ack = fn () => response()->json(['received' => true], 200);

    try {
        switch ($event->type) {

            case 'checkout.session.completed': {
                /** @var \Stripe\Checkout\Session $session */
                $session = $event->data->object;

                $sessionId     = $session->id ?? null;
                $piId          = $session->payment_intent ?? null;
                $reservationId = $session->metadata->reservation_id ?? null;

                if (!$sessionId || !$reservationId) {
                    return $ack();
                }

                DB::transaction(function () use ($event, $sessionId, $piId, $reservationId, $hotelCode, $stripeSecret) {

                    /** @var Reservation|null $reservation */
                    $reservation = Reservation::lockForUpdate()->find($reservationId);
                    if (!$reservation) {
                        return; // ACK: no hay con qué conciliar
                    }

                    $meta = $reservation->meta ?? [];
                    $processed = $meta['stripe_event_ids'] ?? [];

                    // Idempotencia por event.id
                    if (in_array($event->id, $processed, true)) {
                        return;
                    }
                    $processed[] = $event->id;

                    // Persistimos el event.id cuanto antes para evitar loops
                    $reservation->meta = array_merge($meta, ['stripe_event_ids' => $processed]);

                    // Guarda ids Stripe si faltan
                    if (!$reservation->stripe_session_id) {
                        $reservation->stripe_session_id = $sessionId;
                    }
                    if ($piId && !$reservation->stripe_payment_intent_id) {
                        $reservation->stripe_payment_intent_id = $piId;
                    }
                    if (!$reservation->hotel_code) {
                        $reservation->hotel_code = $hotelCode;
                    }

                    $reservationHotelCode = $reservation->hotel_code ?: $hotelCode;

                    // Si ya está final, no re-proceses
                    if (in_array($reservation->status, ['paid', 'failed', 'expired', 'cancelled'], true)) {
                        $reservation->save();
                        return;
                    }

                    // Hold vencido => expira y termina
                    if ($reservation->isHoldExpired()) {
                        $reservation->status = 'expired';
                        $reservation->meta = array_merge($reservation->meta ?? [], [
                            'webhook_note' => 'Hold vencido al recibir checkout.session.completed',
                        ]);
                        $reservation->save();
                        return;
                    }

                    // --- 1) Disponibilidad ---
                    $availability = $this->providerDisponibilidadTipo(
                        hotelCode: $reservationHotelCode,
                        roomTypeCode: $reservation->room_type_code,
                        checkin: $reservation->checkin,
                        checkout: $reservation->checkout,
                        rooms: (int) $reservation->rooms
                    );

                    // Si SOAP falló (timeout/red/XML raro), queremos reintento => lanzamos excepción
                    if (!($availability['ok'] ?? false)) {
                        throw new \RuntimeException('Disponibilidad SOAP error: ' . ($availability['error'] ?? 'unknown'));
                    }

                    // No hay disponibilidad real => NO cobramos, marcamos failed y (opcional) cancel PI si aplica
                    if (!($availability['available'] ?? false)) {

                        if ($piId) {
                            try {
                                $stripeTmp = new StripeClient($stripeSecret);
                                $piTmp = $stripeTmp->paymentIntents->retrieve($piId, []);
                                if ($piTmp->status === 'requires_capture') {
                                    $stripeTmp->paymentIntents->cancel($piId, []);
                                }
                            } catch (\Throwable $e) {
                                // no rompemos el webhook por esto
                                Log::warning('No se pudo cancelar PI al no haber disponibilidad', [
                                    'reservation_id' => $reservation->id,
                                    'pi' => $piId,
                                    'error' => $e->getMessage(),
                                ]);
                            }
                        }

                        $reservation->status = 'failed';
                        $reservation->meta = array_merge($reservation->meta ?? [], [
                            'fail_reason'           => 'no_availability',
                            'availability_response' => $availability['raw'] ?? null,
                            'availability_norm'     => $availability['norm'] ?? null,
                        ]);
                        $reservation->save();
                        return;
                    }

                    if (!$piId) {
                        $reservation->status = 'failed';
                        $reservation->meta = array_merge($reservation->meta ?? [], [
                            'fail_reason' => 'missing_payment_intent',
                        ]);
                        $reservation->save();
                        return;
                    }

                    $stripe = new StripeClient($stripeSecret);

                    // --- 2) Captura (idempotente por captured_at) ---
                    $metaNow = $reservation->meta ?? [];
                    if (empty($metaNow['captured_at'])) {

                        $pi = $stripe->paymentIntents->retrieve($piId, []);

                        if ($pi->status === 'requires_capture') {
                            $pi = $stripe->paymentIntents->capture($piId, []);
                        } elseif ($pi->status !== 'succeeded') {
                            $reservation->status = 'failed';
                            $reservation->meta = array_merge($reservation->meta ?? [], [
                                'fail_reason' => 'payment_intent_not_capturable',
                                'pi_status'   => $pi->status,
                            ]);
                            $reservation->save();
                            return;
                        }

                        $reservation->meta = array_merge($reservation->meta ?? [], [
                            'captured_at'      => now()->toISOString(),
                            'stripe_charge_id' => $pi->latest_charge ?? null,
                        ]);
                        $reservation->save();
                    }

                    // --- 3) Proveedor (idempotente por provider_confirmed_at) ---
                    $metaNow = $reservation->meta ?? [];
                    if (empty($metaNow['provider_confirmed_at'])) {

                        $idTX = (string) $piId;

                        $pagoOk = $this->providerPagoConfirmado(
                            hotelCode: $reservationHotelCode,
                            folio: (string) $reservation->provider_folio,
                            idTX: $idTX,
                            amountCents: (int) $reservation->amount_cents
                        );

                        $cambioOk = false;
                        if ($pagoOk) {
                            $cambioOk = $this->providerCambioStatusVigente(
                                hotelCode: $reservationHotelCode,
                                folio: (string) $reservation->provider_folio,
                                fechaLimite: $reservation->provider_hold_expires_at
                            );
                        }

                        if (!$pagoOk || !$cambioOk) {

                            // Refund idempotente por refunded_at
                            $metaNow = $reservation->meta ?? [];
                            if (empty($metaNow['refunded_at'])) {
                                try {
                                    $stripe->refunds->create(['payment_intent' => $piId]);
                                    $reservation->meta = array_merge($reservation->meta ?? [], [
                                        'refunded_at' => now()->toISOString(),
                                    ]);
                                } catch (\Throwable $e) {
                                    // si el refund falló por algo temporal, conviene reintento
                                    throw new \RuntimeException('Refund fallo: ' . $e->getMessage());
                                }
                            }

                            $reservation->status = 'failed';
                            $reservation->meta = array_merge($reservation->meta ?? [], [
                                'fail_reason'       => 'provider_failed_refunded',
                                'provider_pago_ok'  => $pagoOk,
                                'provider_cambio_ok'=> $cambioOk,
                            ]);
                            $reservation->save();
                            return;
                        }

                        $reservation->meta = array_merge($reservation->meta ?? [], [
                            'provider_confirmed_at' => now()->toISOString(),
                        ]);
                        $reservation->save();
                    }

                    $this->consumeReservationCoupon($reservation);

                    // --- 4) Finalizar ---
                    $reservation->status = 'paid';
                    $reservation->save();

                    // --- 5) Email AFTER COMMIT (no provoca 500) ---
                    DB::afterCommit(function () use ($reservation, $reservationHotelCode) {

                        // reload fresco ya fuera del lock
                        $r = Reservation::find($reservation->id);
                        if (!$r) return;

                        $meta = $r->meta ?? [];
                        if (!empty($meta['email_sent_at'])) return;

                        $recipient = optional($r->user)->email ?? $r->guest_email;
                        $adminRecipient = config(
                            "services.hotels.{$reservationHotelCode}.mail.booking_in_reception_admin_to",
                            'luis@enzomarketing.mx'
                        );

                        if (!$recipient && !$adminRecipient) return;

                        try {
                            if ($recipient) {
                                Mail::mailer('resend')
                                    ->to($recipient)
                                    ->send(new ReservationConfirmedMail($reservation));
                            }

                            if ($adminRecipient) {
                                Mail::mailer('resend')
                                    ->to($adminRecipient)
                                    ->send(new ReservationConfirmedAdminMail($reservation));
                            }

                            $r->meta = array_merge($meta, [
                                'email_sent_at' => now()->toISOString(),
                                'email_to'      => $recipient,
                                'email_admin_to' => $adminRecipient,
                            ]);
                            $r->save();
                        } catch (\Throwable $e) {
                            Log::error('Fallo envío de correo confirmación', [
                                'reservation_id' => $r->id,
                                'email' => [
                                    'guest' => $recipient,
                                    'admin' => $adminRecipient,
                                ],
                                'error' => $e->getMessage(),
                            ]);
                            // NO throw: no queremos 500 por correo
                        }
                    });
                });

                return $ack();
            }

            case 'checkout.session.expired': {
                $session = $event->data->object;
                $sessionId = $session->id ?? null;

                if ($sessionId) {
                    Reservation::where('stripe_session_id', $sessionId)
                        ->whereIn('status', ['awaiting_payment'])
                        ->update(['status' => 'expired']);
                }
                return $ack();
            }

            case 'payment_intent.payment_failed': {
                $pi = $event->data->object;
                $piId = $pi->id ?? null;

                if ($piId) {
                    Reservation::where('stripe_payment_intent_id', $piId)
                        ->whereIn('status', ['awaiting_payment'])
                        ->update([
                            'status' => 'failed',
                            // opcional: guardar motivo
                        ]);
                }
                return $ack();
            }

            case 'payment_intent.canceled': {
                $pi = $event->data->object;
                $piId = $pi->id ?? null;

                if ($piId) {
                    Reservation::where('stripe_payment_intent_id', $piId)
                        ->whereIn('status', ['awaiting_payment'])
                        ->update(['status' => 'cancelled']);
                }
                return $ack();
            }

            default:
                return $ack();
        }

    } catch (\Throwable $e) {
        // Aquí SÍ devolvemos 500 para que Stripe reintente cuando sea un error real/transitorio
        Log::error('Stripe webhook error', [
            'type' => $event->type ?? null,
            'event_id' => $event->id ?? null,
            'error' => $e->getMessage(),
        ]);
        return response('Webhook error: ' . $e->getMessage(), 500);
    }
}


    // =========================
    // SOAP: fDisponibilidadTipo
    // =========================
    private function providerDisponibilidadTipo(string $hotelCode, string $roomTypeCode, $checkin, $checkout, int $rooms): array
    {
        $fc = HotelConfig::fc($hotelCode);
        $endpoint = $fc['soap_endpoint'] ?? null;
        $pass     = $fc['pass'] ?? null;
        $cx       = $fc['cx'] ?? null;

        if (!$endpoint || !$pass || !$cx) {
            return [
                'ok' => false,
                'available' => null,
                'norm' => null,
                'raw' => null,
                'http_status' => null,
                'error' => 'fc_config_incomplete',
            ];
        }

        $checkinDt  = Carbon::parse($checkin)->startOfDay();
        $checkoutDt = Carbon::parse($checkout)->startOfDay();

        $xml = <<<XML
        <?xml version="1.0" encoding="utf-8"?>
        <soap12:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                        xmlns:xsd="http://www.w3.org/2001/XMLSchema"
                        xmlns:soap12="http://www.w3.org/2003/05/soap-envelope">
        <soap12:Body>
            <fDisponibilidadTipo xmlns="https://fcsistemas.com/">
            <lTipo>{$this->xml(strtoupper($roomTypeCode))}</lTipo>
            <lFechaIni>{$this->xml($this->iso($checkinDt))}</lFechaIni>
            <lFechaFin>{$this->xml($this->iso($checkoutDt))}</lFechaFin>
            <lHabs>{$this->xml(max(1, (int)$rooms))}</lHabs>
            <lPassCliente>{$this->xml($pass)}</lPassCliente>
            <lStringCxSAHM>{$this->xml($cx)}</lStringCxSAHM>
            </fDisponibilidadTipo>
        </soap12:Body>
        </soap12:Envelope>
        XML;

        try {
            $resp = Http::timeout((int) ($fc['soap_timeout'] ?? 60))
                ->withHeaders([
                    'Content-Type' => 'application/soap+xml; charset=utf-8',
                    'Accept'       => 'application/soap+xml, text/xml, */*',
                ])
                ->withBody($xml, 'application/soap+xml; charset=utf-8')
                ->post($endpoint);
        } catch (\Throwable $e) {
            // Error de red/timeout/etc => ERROR TEMPORAL (reintentar)
            return [
                'ok'         => false,
                'available'  => null,
                'norm'       => null,
                'raw'        => null,
                'http_status'=> null,
                'error'      => 'http_exception: ' . $e->getMessage(),
            ];
        }

        $raw = $resp->body();

        if (!$resp->ok()) {
            // HTTP != 200 => ERROR TEMPORAL (reintentar)
            return [
                'ok'         => false,
                'available'  => null,
                'norm'       => null,
                'raw'        => $raw,
                'http_status'=> $resp->status(),
                'error'      => 'http_status_not_ok',
            ];
        }

        // Intentamos leer el nodo esperado
        try {
            $result = $this->parseSoapString($raw, 'fDisponibilidadTipoResult');
        } catch (\Throwable $e) {
            // XML malformado => ERROR TEMPORAL (reintentar)
            return [
                'ok'         => false,
                'available'  => null,
                'norm'       => null,
                'raw'        => $raw,
                'http_status'=> $resp->status(),
                'error'      => 'xml_parse_error: ' . $e->getMessage(),
            ];
        }

        if ($result === null) {
            // No vino el nodo => ERROR TEMPORAL (reintentar)
            return [
                'ok'         => false,
                'available'  => null,
                'norm'       => null,
                'raw'        => $raw,
                'http_status'=> $resp->status(),
                'error'      => 'missing_result_node',
            ];
        }

        $norm = strtoupper(trim(preg_replace('/\s+/', ' ', $result)));

        // DISPONIBILIDAD => OK + disponible
        if ($norm === 'DISPONIBILIDAD') {
            return [
                'ok'         => true,
                'available'  => true,
                'norm'       => $norm,
                'raw'        => $raw,
                'http_status'=> $resp->status(),
                'error'      => null,
            ];
        }

        // Cualquier otro texto => OK + NO disponible
        return [
            'ok'         => true,
            'available'  => false,
            'norm'       => $norm,
            'raw'        => $raw,
            'http_status'=> $resp->status(),
            'error'      => null,
        ];
    }

    private function consumeReservationCoupon(Reservation $reservation): void
    {
        $meta = $reservation->meta ?? [];
        $coupon = $meta['coupon'] ?? null;
        $code = $coupon['code'] ?? null;

        if (!$code || !empty($coupon['consumed_at'])) {
            return;
        }

        app(CuponService::class)->consumeCoupon($code);

        $meta['coupon']['consumed_at'] = now()->toISOString();
        $reservation->meta = $meta;
    }



    // =========================
    // SOAP: PagoConfirmado / CambioStatus
    // (copiados de tu estilo en CheckoutController)
    // =========================
    private function providerPagoConfirmado(string $hotelCode, string $folio, string $idTX, int $amountCents): bool
    {
        $fc = HotelConfig::fc($hotelCode);
        $endpoint = $fc['soap_endpoint'] ?? null;
        $pass     = $fc['pass'] ?? null;
        $cx       = $fc['cx'] ?? null;

        if (!$endpoint || !$pass || !$cx) {
            return false;
        }

        $importe  = number_format($amountCents / 100, 2, '.', '');
        $concepto = 'TARJETA CREDITO';

        $xml = <<<XML
        <?xml version="1.0" encoding="utf-8"?>
        <soap12:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap12="http://www.w3.org/2003/05/soap-envelope">
        <soap12:Body>
            <fPagoConfirmado xmlns="https://fcsistemas.com/">
            <lDato>{$this->xml($folio)}</lDato>
            <idTX>{$this->xml($idTX)}</idTX>
            <lImportePago>{$this->xml($importe)}</lImportePago>
            <lConceptoPago>{$this->xml($concepto)}</lConceptoPago>
            <lPassCliente>{$this->xml($pass)}</lPassCliente>
            <lStringCxSAHM>{$this->xml($cx)}</lStringCxSAHM>
            </fPagoConfirmado>
        </soap12:Body>
        </soap12:Envelope>
        XML;

        $resp = Http::timeout((int) ($fc['soap_timeout'] ?? 60))
            ->withHeaders([
                'Content-Type' => 'application/soap+xml; charset=utf-8',
            ])
            ->withBody($xml, 'application/soap+xml; charset=utf-8')
            ->post($endpoint);

        if (!$resp->ok()) return false;

        return $this->parseSoapBoolean($resp->body(), 'fPagoConfirmadoResult', false);
    }

    private function providerCambioStatusVigente(string $hotelCode, string $folio, ?\DateTimeInterface $fechaLimite): bool
    {
        $fc = HotelConfig::fc($hotelCode);
        $endpoint = $fc['soap_endpoint'] ?? null;
        $pass     = $fc['pass'] ?? null;
        $cx       = $fc['cx'] ?? null;
        $dummyCc  = $fc['dummy_cc'] ?? '0000000000000000';

        if (!$endpoint || !$pass || !$cx) {
            return false;
        }

        $limiteIso = ($fechaLimite ? Carbon::parse($fechaLimite) : now())->format('Y-m-d\TH:i:s');

        $xml = <<<XML
        <?xml version="1.0" encoding="utf-8"?>
        <soap12:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                        xmlns:xsd="http://www.w3.org/2001/XMLSchema"
                        xmlns:soap12="http://www.w3.org/2003/05/soap-envelope">
        <soap12:Body>
            <fCambioStatusReserva xmlns="https://fcsistemas.com/">
            <lIdReservacion>{$this->xml($folio)}</lIdReservacion>
            <lStatus>VIGENTE</lStatus>
            <lTentativa>false</lTentativa>
            <lNoTarjeta>{$this->xml($dummyCc)}</lNoTarjeta>
            <lFechaLimite>{$this->xml($limiteIso)}</lFechaLimite>
            <lPassCliente>{$this->xml($pass)}</lPassCliente>
            <lStringCxSAHM>{$this->xml($cx)}</lStringCxSAHM>
            </fCambioStatusReserva>
        </soap12:Body>
        </soap12:Envelope>
        XML;

        $resp = Http::timeout((int) ($fc['soap_timeout'] ?? 60))
            ->withHeaders([
                'Content-Type' => 'application/soap+xml; charset=utf-8',
            ])
            ->withBody($xml, 'application/soap+xml; charset=utf-8')
            ->post($endpoint);

        if (!$resp->ok()) return false;

        // El proveedor devuelve texto (ej. "Status modificado"), no booleano
        $result = $this->parseSoapString($resp->body(), 'fCambioStatusReservaResult')
            ?? $this->parseSoapString($resp->body(), 'string'); // fallback típico ASMX

        if ($result === null) return false;

        $norm = strtolower(trim($result));

        // Acepta variaciones comunes
        if (in_array($norm, ['true', '1', 'ok', 'status modificado'], true)) return true;

        // Tu caso real:
        return str_contains($norm, 'modificado');
    }


    // =========================
    // Helpers SOAP
    // =========================
    private function parseSoapBoolean(string $xmlBody, string $resultNodeName, bool $defaultTrueWhenMissing = false): bool
    {
        $doc = new \DOMDocument();
        $doc->loadXML($xmlBody);

        $xp = new \DOMXPath($doc);
        $value = trim((string) $xp->evaluate('string(//*[local-name()="' . $resultNodeName . '"])'));

        if ($value === '') return $defaultTrueWhenMissing;

        $valueLower = strtolower($value);
        return in_array($valueLower, ['true', '1', 'yes', 'si'], true);
    }

    private function parseSoapString(string $xmlBody, string $nodeName): ?string
    {
        $doc = new \DOMDocument();
        $doc->loadXML($xmlBody);

        $xp = new \DOMXPath($doc);
        $value = trim((string) $xp->evaluate('string(//*[local-name()="' . $nodeName . '"])'));

        return $value !== '' ? $value : null;
    }

    private function iso($d): string
    {
        return Carbon::parse($d)->format('Y-m-d\TH:i:s');
    }

    private function xml($s): string
    {
        return htmlspecialchars((string) $s, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
