<?php

namespace App\Http\Controllers;

use App\Mail\BookingInReceptionAdminMail;
use App\Mail\BookingInReceptionCustomerMail;
use App\Mail\ReservationConfirmedMail;
use App\Models\Reservation;
use App\Services\CuponService;
use App\Services\HotelConfig;
use App\Services\PricingService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Resend\Laravel\Facades\Resend;
use Stripe\StripeClient;
use Throwable;

class CheckoutController extends Controller
{
    public function create(Request $request)
    {
        $data = $request->validate([
            'amount'         => ['required', 'integer', 'min:100'],     // centavos
            'currency'       => ['required', 'string', 'size:3'],       // 'MXN'
            'hotel_code'      => ['required', 'string', Rule::in(HotelConfig::codes())],
            'room_type_code' => ['required', 'string', 'max:10'],
            'checkin'        => ['required', 'string'],                 // 'YYYY-MM-DD'
            'checkout'       => ['required', 'string'],                 // 'YYYY-MM-DD'
            'rooms'          => ['required', 'integer', 'min:1'],
            'adults'         => ['required', 'integer', 'min:1'],
            'coupon_code'    => ['nullable', 'string', 'max:100'],
            'userInfo'       => ['nullable', 'array'],
            // 'userInfo.email' => ['nullable', 'email'],
        ]);

        $data['hotel_code'] = HotelConfig::normalize($data['hotel_code']);

        $customerName = auth()->user()->name ?? ($data['userInfo']['name'] ?? null);
        $customerLastName = auth()->user()->lastname ?? ($data['userInfo']['lastname'] ?? null);
        $customerPhone = auth()->user()->phone ?? ($data['userInfo']['phone'] ?? null);
        // Determinar email del cliente
        $customerEmail = auth()->check()
            ? auth()->user()->email
            : ($data['userInfo']['email'] ?? null);
        $customerCp = auth()->user()->cp ?? ($data['userInfo']['cp'] ?? null);
        $customerStateId = auth()->user()->state ?? ($data['userInfo']['state'] ?? null);
        $customerCityId = auth()->user()->city ?? ($data['userInfo']['city'] ?? null);

        if (!$customerEmail) {
            return response()->json([
                'success' => false,
                'message' => 'No se pudo determinar el correo del cliente.',
            ], 422);
        }

        $pricing = app(PricingService::class)->buildPricingData($data);
        $pricedData = array_merge($data, [
            'amount' => (int) $pricing['final_cents'],
        ]);

        // Construir datos del cliente (para el hold)
        $customer = [
            'email'     => $customerEmail,
            'name'      => $customerName,
            'lastname'  => $customerLastName,
            'phone'     => $customerPhone,
            'cp'        => $customerCp,
            // IDs de estado/ciudad que tú ya obtienes con tus endpoints:
            'state_id'  => $customerStateId,
            'city_id'   => $customerCityId,
            'country'   => 'MX',
        ];

        // $xmlPreview = $this->buildHoldXmlPreview($data, $customer);
        // return response()->json([
        //     'ok'  => true,
        //     'xml' => $xmlPreview,
        // ]);

        // 1) Crear HOLD con el proveedor
        try {
            $hold = $this->createProviderHold($pricedData, $customer);
        } catch (Throwable $e) {
            // Log opcional: \Log::error('Hold error', ['e' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'No se pudo generar la reservación temporal (hold) con el proveedor.',
            ], 502);
        }

        // 2) Guardar en BD la reserva local
        $nights = max(1, Carbon::parse($data['checkin'])->diffInDays(Carbon::parse($data['checkout'])));
        $hotelFc = HotelConfig::fc($data['hotel_code']);
        $minutes   = (int) ($hotelFc['hold_ttl_minutes'] ?? 30);
        $expiresAt = Carbon::now()->addMinutes($minutes);

        $user       = auth()->user();
        $isLoggedIn = (bool) auth()->check();
        $userId     = $user?->id;

        $reservation = Reservation::create([
            'user_id'                  => $userId,
            'hotel_code'               => $data['hotel_code'],
            'guest_name'               => $isLoggedIn ? null : $customerName,
            'guest_email'              => $isLoggedIn ? null : $customerEmail,
            'guest_phone'              => $isLoggedIn ? null : $customerPhone,
            'origin_page'              => $this->originPage($request),

            'room_type_code'             => strtoupper($data['room_type_code']),
            'checkin'                    => $data['checkin'],
            'checkout'                   => $data['checkout'],
            'nights'                     => $nights,
            'rooms'                      => (int) $data['rooms'],
            'adults'                     => (int) $data['adults'],

            'amount_cents'               => (int) $pricing['final_cents'],
            'currency'                   => strtoupper($data['currency']),

            'provider_folio'             => (string) $hold['folio'],
            'provider_hold_expires_at'   => $hold['expires_at'] ?? $expiresAt,

            'status'                     => 'awaiting_payment',

            'client_order_key'           => (string) Str::uuid(),
            'meta'                       => $pricing['meta'],
        ]);

        // 3) Crear sesión de Stripe
        $stripe = new StripeClient(HotelConfig::stripeSecret($reservation->hotel_code));

        $session = $stripe->checkout->sessions->create([
            'mode'        => 'payment',

            'expires_at' => $reservation->provider_hold_expires_at->timestamp,

            'payment_intent_data' => [
                'capture_method' => 'manual',
            ],
            'success_url' => route('checkout.success') . '?hotel=' . urlencode($reservation->hotel_code) . '&session_id={CHECKOUT_SESSION_ID}',
            'cancel_url'  => route('checkout.cancel') . '?hotel=' . urlencode($reservation->hotel_code) . '&session_id={CHECKOUT_SESSION_ID}',
            'line_items'  => [[
                'price_data'  => [
                    'currency'     => strtolower($data['currency']),
                    'product_data' => ['name' => 'Reserva de habitación'],
                    'unit_amount'  => (int) $pricing['final_cents'],
                ],
                'quantity'    => 1,
            ]],
            'metadata'    => [
                'reservation_id'   => (string) $reservation->id,
                'hotel_code'        => (string) $reservation->hotel_code,
                'provider_folio'    => (string) $reservation->provider_folio,
                'room_type_code'    => (string) $reservation->room_type_code,
                'checkin'           => (string) $reservation->checkin,
                'checkout'          => (string) $reservation->checkout,
                'rooms'             => (string) $reservation->rooms,
                'adults'            => (string) $reservation->adults,
                'coupon_code'       => (string) ($pricing['coupon']['code'] ?? ''),
                'user_id'           => auth()->id() ?: '',
            ],
            'customer_email' => $customerEmail,
        ]);

        // Guardar IDs de Stripe en la reserva
        $reservation->update([
            'stripe_session_id'  => $session->id,
            'stripe_checkout_url' => $session->url,
        ]);

        return response()->json(['url' => $session->url]);
    }

    public function success(Request $request)
    {
        $sessionId = $request->query('session_id');
        if (!$sessionId) {
            abort(400, 'Falta session_id');
        }

        // 1) Busca por session_id (lo normal)
        $reservation = Reservation::where('stripe_session_id', $sessionId)->first();
        $hotelCode = $reservation?->hotel_code ?? $request->query('hotel');
        $stripe = new \Stripe\StripeClient(HotelConfig::stripeSecret($hotelCode));

        // Solo para UI (estado de Stripe); NO lo uses como “fuente de verdad”
        $session = $stripe->checkout->sessions->retrieve($sessionId);

        // 2) Fallback: si por alguna razón no está, intenta por metadata.reservation_id
        if (!$reservation) {
            $reservationId = $session->metadata->reservation_id ?? null;
            if ($reservationId) {
                $reservation = Reservation::find($reservationId);

                // si la encontraste, amarra el session_id
                if ($reservation && !$reservation->stripe_session_id) {
                    $reservation->update(['stripe_session_id' => $sessionId]);
                }
            }
        }

        if (!$reservation) {
            // Renderiza la vista aunque no exista (mejor UX que un JSON suelto)
            return Inertia::render('Checkout/Thanks', [
                'reservation' => null,
                'sessionId'   => $sessionId,
                'stripe'      => [
                    'status'         => $session->status ?? null,
                    'payment_status' => $session->payment_status ?? null,
                ],
            ]);
        }

        // Reconciliar IDs (sin cambiar estado)
        $updates = [];

        $piId = $session->payment_intent ?? null; // string id
        if ($piId && !$reservation->stripe_payment_intent_id) {
            $updates['stripe_payment_intent_id'] = (string) $piId;
            $updates['is_confirmed'] = true;
        }

        if ($updates) {
            $reservation->update($updates);
            $reservation->refresh();
        }

        return Inertia::render('Checkout/Thanks', [
            'reservation' => $reservation->only([
                'id',
                'status',
                'provider_folio',
                'checkin',
                'checkout',
                'amount_cents',
                'currency',
                'hotel_code',
                'nights',
                'rooms',
                'adults',
                'room_type_code',
            ]),
            'sessionId' => $sessionId,
            'stripe' => [
                'status'         => $session->status ?? null,
                'payment_status' => $session->payment_status ?? null,
                'payment_intent' => $piId,
            ],
        ]);
    }


    /**
     * Crea el HOLD con el proveedor (fInsertaReservaNew) y devuelve folio + expiración.
     * Lanza excepción si falla.
     */
    private function createProviderHold(array $data, array $customer): array
    {
        $fc         = HotelConfig::fc($data['hotel_code']);
        $endpoint   = $fc['soap_endpoint'] ?? null;
        $rateName   = $fc['rate_name'] ?? 'WWW_CA';
        $pass       = $fc['pass'] ?? null;
        $cx         = $fc['cx'] ?? null;

        // Dummy CC desde config/env (por defecto, 16 ceros)
        $dummyCc    = $fc['dummy_cc'] ?? '0000000000000000';

        if (!$endpoint || !$pass || !$cx) {
            throw new \RuntimeException('Configuracion FC incompleta para ' . HotelConfig::name($data['hotel_code']));
        }

        $checkinDt  = \Carbon\Carbon::parse($data['checkin']);
        $checkoutDt = \Carbon\Carbon::parse($data['checkout']);
        $nights     = max(1, $checkinDt->diffInDays($checkoutDt));
        $roomsCount = max(1, (int) ($data['rooms'] ?? 1));

        $totalPesos     = ((int) $data['amount']) / 100;
        $pricePerNight  = round($totalPesos / ($nights * $roomsCount), 2);

        $minutes   = (int) ($fc['hold_ttl_minutes'] ?? 30);
        $expiresAt = now()->addMinutes($minutes);

        $iso = fn($d) => \Carbon\Carbon::parse($d)->format('Y-m-d\TH:i:s');

        $xml = <<<XML
            <?xml version="1.0" encoding="utf-8"?>
            <soap12:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap12="http://www.w3.org/2003/05/soap-envelope">
            <soap12:Body>
                <fInsertaReservaNew xmlns="https://fcsistemas.com/">
                <lTipo>{$this->xml($data['room_type_code'])}</lTipo>
                <lFechaIni>{$this->xml($iso($checkinDt))}</lFechaIni>
                <lFechaFin>{$this->xml($iso($checkoutDt))}</lFechaFin>
                <lHabs>{$roomsCount}</lHabs>
                <lAdu>{$this->xml($data['adults'])}</lAdu>
                <lMen>0</lMen>
                <lTarifa>{$this->xml(number_format($pricePerNight, 2, '.', ''))}</lTarifa>
                <lNotas>Reserva web</lNotas>
                <lEmail>{$this->xml($customer['email'])}</lEmail>
                <lPassCliente>{$this->xml($pass)}</lPassCliente>
                <lPersona>
                    <id_Persona>0</id_Persona>
                    <Nombre_Persona>{$this->xml($customer['name'] ?? '')}</Nombre_Persona>
                    <APat_Persona>{$this->xml($customer['lastname'] ?? '')}</APat_Persona>
                    <Tel1>{$this->xml($customer['phone'] ?? '')}</Tel1>
                    <Email>{$this->xml($customer['email'])}</Email>
                    <CP>{$this->xml($customer['cp'] ?? '')}</CP>
                    <CiudadID>{$this->xml($customer['city_id'] ?? 0)}</CiudadID>
                    <EstadoID>{$this->xml($customer['state_id'] ?? 0)}</EstadoID>
                    <PaisID>{$this->xml($customer['country'] ?? 'MX')}</PaisID>
                    <ClienteID>0</ClienteID>
                    <DomID>0</DomID>
                </lPersona>
                <lStringCxSAHM>{$this->xml($cx)}</lStringCxSAHM>
                <lTarifaNombre>{$this->xml($rateName)}</lTarifaNombre>
                <lTransporteAeropuerto>false</lTransporteAeropuerto>
                <lHoraVuelo>00:00</lHoraVuelo>
                <lNoVueloAeropuerto>no</lNoVueloAeropuerto>
                <lComentariosAeropuerto>no</lComentariosAeropuerto>
                <lNoEnviarConfirmacion>false</lNoEnviarConfirmacion>
                <lNoTarjeta>{$this->xml($dummyCc)}</lNoTarjeta>
                <lFechaLimite>{$this->xml($iso($expiresAt))}</lFechaLimite>
                </fInsertaReservaNew>
            </soap12:Body>
            </soap12:Envelope>
            XML;

        $this->debugSoap('fInsertaReservaNew.request', [
            'hotel_code' => $data['hotel_code'],
            'endpoint' => $endpoint,
            'room_type_code' => $data['room_type_code'],
            'checkin' => (string) $data['checkin'],
            'checkout' => (string) $data['checkout'],
            'rooms' => $roomsCount,
            'adults' => (int) $data['adults'],
            'amount_cents' => (int) $data['amount'],
            'price_per_night' => number_format($pricePerNight, 2, '.', ''),
            'rate_name' => $rateName,
            'cx' => $cx,
            'pass' => $pass,
            'customer' => $customer,
            'xml' => $xml,
        ]);

        $resp = \Illuminate\Support\Facades\Http::timeout((int) ($fc['soap_timeout'] ?? 60))
            ->withHeaders([
                'Content-Type' => 'application/soap+xml; charset=utf-8',
            ])
            ->withBody($xml, 'application/soap+xml; charset=utf-8')
            ->post($endpoint);

        $this->debugSoap('fInsertaReservaNew.response', [
            'hotel_code' => $data['hotel_code'],
            'endpoint' => $endpoint,
            'http_status' => $resp->status(),
            'body' => $resp->body(),
        ]);

        if (!$resp->ok()) {
            throw new \RuntimeException('SOAP hold error: HTTP ' . $resp->status());
        }

        $folio = $this->parseFolioFromHoldResponse($resp->body());
        if ($folio === null) {
            throw new \RuntimeException('SOAP hold error: no se pudo extraer el folio.');
        }

        return [
            'folio'      => $folio,
            'expires_at' => $expiresAt,
        ];
    }

    private function parseFolioFromHoldResponse(string $xmlBody): ?string
    {
        $doc = new \DOMDocument();
        $doc->loadXML($xmlBody);

        $xp = new \DOMXPath($doc);
        $value = trim((string) $xp->evaluate('string(//*[local-name()="fInsertaReservaNewResult"])'));
        // En tus ejemplos es un número como "696"
        return $value !== '' ? $value : null;
    }

    private function xml($s): string
    {
        return htmlspecialchars((string)$s, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    private function providerPagoConfirmado(string $folio, string $idTX, int $amountCents, string $hotelCode = HotelConfig::DEFAULT_CODE): bool
    {
        $fc = HotelConfig::fc($hotelCode);
        $endpoint = $fc['soap_endpoint'] ?? null;
        $pass     = $fc['pass'] ?? null;
        $cx       = $fc['cx'] ?? null;

        if (!$endpoint || !$pass || !$cx) {
            return false;
        }

        $importe = number_format($amountCents / 100, 2, '.', ''); // MXN con 2 decimales
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

        $this->debugSoap('fPagoConfirmado.request', [
            'hotel_code' => $hotelCode,
            'endpoint' => $endpoint,
            'folio' => $folio,
            'idTX' => $idTX,
            'amount_cents' => $amountCents,
            'importe' => $importe,
            'cx' => $cx,
            'pass' => $pass,
            'xml' => $xml,
        ]);

        $resp = Http::timeout((int) ($fc['soap_timeout'] ?? 60))
            ->withHeaders([
                'Content-Type' => 'application/soap+xml; charset=utf-8',
            ])
            ->withBody($xml, 'application/soap+xml; charset=utf-8')
            ->post($endpoint);

        $this->debugSoap('fPagoConfirmado.response', [
            'hotel_code' => $hotelCode,
            'endpoint' => $endpoint,
            'http_status' => $resp->status(),
            'body' => $resp->body(),
        ]);

        if (!$resp->ok()) {
            // \Log::warning('fPagoConfirmado HTTP error', ['status' => $resp->status(), 'body' => $resp->body()]);
            return false;
        }

        // Esperamos <fPagoConfirmadoResult>true</fPagoConfirmadoResult>
        return $this->parseSoapBoolean($resp->body(), 'fPagoConfirmadoResult');
    }

    /**
     * Llama a fCambioStatusReserva (VIGENTE).
     */
    private function providerCambioStatusVigente(string $folio, ?\DateTimeInterface $fechaLimite, string $hotelCode = HotelConfig::DEFAULT_CODE): bool
    {
        $fc = HotelConfig::fc($hotelCode);
        $endpoint = $fc['soap_endpoint'] ?? null;
        $pass     = $fc['pass'] ?? null;
        $cx       = $fc['cx'] ?? null;
        $dummyCc  = $fc['dummy_cc'] ?? '0000000000000000';

        if (!$endpoint || !$pass || !$cx) {
            return false;
        }

        $limiteIso = ($fechaLimite ? \Carbon\Carbon::parse($fechaLimite) : now())
            ->format('Y-m-d\TH:i:s');

        $xml = <<<XML
        <?xml version="1.0" encoding="utf-8"?>
        <soap12:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap12="http://www.w3.org/2003/05/soap-envelope">
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

        $this->debugSoap('fCambioStatusReserva.request', [
            'hotel_code' => $hotelCode,
            'endpoint' => $endpoint,
            'folio' => $folio,
            'fecha_limite' => $limiteIso,
            'cx' => $cx,
            'pass' => $pass,
            'xml' => $xml,
        ]);

        $resp = Http::timeout((int) ($fc['soap_timeout'] ?? 60))
            ->withHeaders([
                'Content-Type' => 'application/soap+xml; charset=utf-8',
            ])
            ->withBody($xml, 'application/soap+xml; charset=utf-8')
            ->post($endpoint);

        $this->debugSoap('fCambioStatusReserva.response', [
            'hotel_code' => $hotelCode,
            'endpoint' => $endpoint,
            'http_status' => $resp->status(),
            'body' => $resp->body(),
        ]);

        if (!$resp->ok()) {
            // \Log::warning('fCambioStatusReserva HTTP error', ['status' => $resp->status(), 'body' => $resp->body()]);
            return false;
        }

        // Muchos servicios devuelven <fCambioStatusReservaResult>true</fCambioStatusReservaResult>
        // Si tu instancia devuelve otra cosa, ajusta el nombre del nodo aquí.
        return $this->parseSoapBoolean($resp->body(), 'fCambioStatusReservaResult', defaultTrueWhenMissing: true);
    }

    /**
     * Extrae un booleano de un nodo result dentro de un sobre SOAP.
     */
    private function parseSoapBoolean(string $xmlBody, string $resultNodeName, bool $defaultTrueWhenMissing = false): bool
    {
        $doc = new \DOMDocument();
        $doc->loadXML($xmlBody);

        $xp = new \DOMXPath($doc);

        // Busca el nodo por local-name para ignorar namespaces
        $value = trim((string) $xp->evaluate('string(//*[local-name()="' . $resultNodeName . '"])'));

        if ($value === '') {
            // Si no viene el nodo, decide política (algunos endpoints no devuelven boolean explícito)
            return $defaultTrueWhenMissing;
        }

        $valueLower = strtolower($value);
        return in_array($valueLower, ['true', '1', 'yes', 'si'], true);
    }

    private function debugSoap(string $event, array $context): void
    {
        $hotelCode = $context['hotel_code'] ?? null;
        $enabled = $hotelCode
            ? (HotelConfig::fc($hotelCode)['soap_debug'] ?? false)
            : config('services.fc.soap_debug', false);

        if (!filter_var($enabled, FILTER_VALIDATE_BOOL)) {
            return;
        }

        Log::warning($event, $context);

        try {
            $payload = [
                'timestamp' => now()->toIso8601String(),
                'event' => $event,
                'context' => $context,
            ];

            file_put_contents(
                storage_path('logs/soap-debug.log'),
                json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL,
                FILE_APPEND
            );
        } catch (\Throwable $e) {
            Log::error('soap-debug.write_failed', [
                'event' => $event,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function status(Request $request)
    {
        $data = $request->validate([
            'session_id' => ['required', 'string'],
        ]);

        $reservation = Reservation::where('stripe_session_id', $data['session_id'])->first();

        if (!$reservation) {
            return response()
                ->json(['ok' => false, 'message' => 'Reserva no encontrada'])
                ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
                ->setStatusCode(404);
        }

        // Si está logeado y la reserva pertenece a otro usuario, bloquea.
        if (auth()->check() && $reservation->user_id && $reservation->user_id !== auth()->id()) {
            abort(403);
        }

        $finalStatuses = ['paid', 'failed', 'expired', 'cancelled'];
        $meta = $reservation->meta ?? [];

        return response()
            ->json([
                'ok' => true,
                'reservation' => [
                    'id'         => $reservation->id,
                    'status'     => $reservation->status,
                    'updated_at' => optional($reservation->updated_at)->toISOString(),

                    'provider_folio' => $reservation->provider_folio,
                    'hotel_code' => $reservation->hotel_code,

                    // Campos para pintar el resumen en UI:
                    'room_type_code' => $reservation->room_type_code,
                    'checkin'        => (string) $reservation->checkin,
                    'checkout'       => (string) $reservation->checkout,
                    'nights'         => (int) $reservation->nights,
                    'rooms'          => (int) $reservation->rooms,
                    'adults'         => (int) $reservation->adults,
                    'amount_cents'   => (int) $reservation->amount_cents,
                    'currency'       => (string) $reservation->currency,

                    // Útil si quieres mostrar “vence a las …”
                    'provider_hold_expires_at' => optional($reservation->provider_hold_expires_at)->toISOString(),

                    // Para que tu computed contactEmail funcione aunque sea invitado
                    'guest_email' => $reservation->guest_email,
                ],
                'is_final'    => in_array($reservation->status, $finalStatuses, true),
                'fail_reason' => $meta['fail_reason'] ?? null,

                // (Opcional) si aplicas la Opción A del webhook:
                'availability_response' => $meta['availability_response'] ?? null,
                'availability_norm'     => $meta['availability_norm'] ?? null,
            ])
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
    }

    public function cancel(Request $request)
    {
        // Si Stripe llega con session_id (si lo agregas en cancel_url), cancelamos la reserva.
        $sessionId = $request->query('session_id');

        if ($sessionId) {
            Reservation::where('stripe_session_id', $sessionId)
                ->whereIn('status', ['awaiting_payment'])
                ->update(['status' => 'cancelled']);
        }

        // Render/redirect a donde prefieras
        // return Inertia::render('Checkout/Cancelled');
        // return Inertia::render('Home');
        return redirect()->route('index');
    }

    public function pagoEnRecepcion(Request $request) {
        return Inertia::render('Checkout/ThanksReception', $request->all());
    }

    public function bookingInReception(Request $request)
    {
        $data = $request->validate([
            'hotel_code' => ['required', 'string', Rule::in(HotelConfig::codes())],
            'room_code' => ['required', 'string'],
            'room_name' => ['required', 'string'],
            'plan' => ['nullable', 'string'],
            'check_in' => ['required', 'date'],
            'check_out' => ['required', 'date'],
            'adults' => ['required', 'integer', 'min:1'],
            'num_habs' => ['required', 'integer', 'min:1'],
            'amount_cents' => ['required', 'integer', 'min:100'],
            'subtotal_cents' => ['nullable', 'integer', 'min:100'],
            'amount' => ['nullable', 'numeric'],
            'coupon_code' => ['nullable', 'string', 'max:100'],
            'user_info' => ['required', 'array'],
            'user_info.name' => ['required', 'string'],
            'user_info.lastname' => ['required', 'string'],
            'user_info.email' => ['required', 'email'],
            'user_info.phone' => ['required', 'string'],
        ]);

        $data['hotel_code'] = HotelConfig::normalize($data['hotel_code']);
        $data['hotel_name'] = HotelConfig::name($data['hotel_code']);
        $pricing = app(PricingService::class)->buildPricingData([
            'amount' => (int) ($data['subtotal_cents'] ?? $data['amount_cents']),
            'hotel_code' => $data['hotel_code'],
            'room_type_code' => $data['room_code'],
            'checkin' => $data['check_in'],
            'checkout' => $data['check_out'],
            'coupon_code' => $data['coupon_code'] ?? null,
        ]);
        $bookingReceptionAdminTo = config(
            "services.hotels.{$data['hotel_code']}.mail.booking_in_reception_admin_to",
            'luis@enzomarketing.mx'
        );

        $reservation = Reservation::create([
            'hotel_code' => $data['hotel_code'],
            'room_type_code' => $data['room_code'],
            'checkin' => $data['check_in'],
            'checkout' => $data['check_out'],
            'nights' => (new \DateTime($data['check_out']))->diff(new \DateTime($data['check_in']))->days,
            'rooms' => $data['num_habs'],
            'adults' => $data['adults'],
            'guest_name' => $data['user_info']['name'] . ' ' . $data['user_info']['lastname'],
            'guest_email' => $data['user_info']['email'],
            'guest_phone' => $data['user_info']['phone'],
            'origin_page' => $this->originPage($request),
            'amount_cents' => (int) $pricing['final_cents'],
            'provider_folio' => 'RECEPCION-' . strtoupper(uniqid()),
            'provider_hold_expires_at' => now()->addHours(2),
            'status' => 'booking_in_reception',
            'client_order_key' => (string) Str::uuid(),
            'meta' => $pricing['meta'],
        ]);

        if (!empty($pricing['coupon']['code'])) {
            app(CuponService::class)->consumeCoupon($pricing['coupon']['code']);
            $meta = $reservation->meta ?? [];
            $meta['coupon']['consumed_at'] = now()->toISOString();
            $reservation->update(['meta' => $meta]);
        }

        Resend::emails()->send([
            'from' => 'Nuve Hotel <no-reply@nuvehotel.com>',
            'to' => $data['user_info']['email'],
            'subject' => 'Reserva con pago en recepción',
            'html' => (new BookingInReceptionCustomerMail($data))->render(),
        ]);

        Resend::emails()->send([
            'from' => 'Nuve Hotel <no-reply@nuvehotel.com>',
            'to' => $bookingReceptionAdminTo,
            'subject' => 'Reserva con pago en recepción',
            'html' => (new BookingInReceptionAdminMail($data))->render(),
        ]);

        return response()->json([
            'message' => 'Reserva creada con exito para pago en recepcion.',
            'amount_cents' => (int) $pricing['final_cents'],
            'coupon' => $pricing['coupon'],
        ], 201);
    }

    private function originPage(Request $request): string
    {
        return (string) ($request->headers->get('referer') ?: $request->fullUrl());
    }
}


