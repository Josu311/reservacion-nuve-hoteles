<?php

namespace App\Http\Controllers;

use App\Services\CuponService;
use App\Services\HotelConfig;
use App\Services\PricingService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class ReservaController extends Controller
{
    /**
     * GET /disponibilidad
     * Renderiza la página con TODAS las habitaciones y sus tarifas por fecha.
     */
    public function index(Request $request)
    {
        return $this->renderAvailability($request, ['torreon', 'gomez'], [
            'search_path' => route('disponibilidad.consultar'),
            'page_title' => 'Disponibilidad de habitaciones',
            'hero_title' => 'Reserva tu habitación',
            'hero_badge' => 'Nuve Hoteles',
            'hero_image' => '/img/home-1.webp',
            'use_parras_branding' => false,
        ]);
    }

    public function hotelIndex(Request $request, string $hotel)
    {
        $hotelCode = HotelConfig::normalize($hotel);

        return $this->renderAvailability($request, [$hotelCode], [
            'search_path' => route("{$hotelCode}.disponibilidad.consultar"),
            'page_title' => 'Disponibilidad en ' . HotelConfig::name($hotelCode),
            'hero_title' => HotelConfig::name($hotelCode),
            'hero_badge' => 'Disponibilidad',
            'hero_image' => $hotelCode === 'parras' ? '/img/hotels-38.webp' : '/img/home-1.webp',
            'use_parras_branding' => $hotelCode === 'parras',
        ]);
    }

    /**
     * POST /disponibilidad
     * Valida el formulario y redirige con query string al GET.
     */
    public function validateDataUserHabs(Request $request)
    {
        $data = $this->validateAvailabilityRequest($request);

        return to_route('disponibilidad.index', $data);
    }

    public function validateDataUserHabsForHotel(Request $request, string $hotel)
    {
        $data = $this->validateAvailabilityRequest($request);
        $hotelCode = HotelConfig::normalize($hotel);

        return to_route("{$hotelCode}.disponibilidad.index", $data);
    }

    public function validateCoupon(Request $request, CuponService $coupons)
    {
        $data = $request->validate([
            'coupon_code' => ['required', 'string', 'max:100'],
        ]);

        $discount = $coupons->buildDiscountData($data['coupon_code'], 0);

        return response()->json([
            'code' => $discount['code'],
            'discount_type' => $discount['discount_type'],
            'discount_value' => $discount['discount_value'],
        ]);
    }

    /**
     * Llama al método SOAP GetHabitacionTarifasFechasFotoDescrip_ES_EN (todas las habitaciones).
     * Devuelve el XML de la respuesta o lanza excepción en error.
     */
    private function callSoapTarifasFechas(array $p, string $hotelCode, bool $multipleHotels = false): string
    {
        $fc = HotelConfig::fc($hotelCode);

        if (empty($fc['soap_endpoint']) || empty($fc['pass']) || empty($fc['cx'])) {
            throw new \RuntimeException('Configuracion FC incompleta para ' . HotelConfig::name($hotelCode));
        }

        $fechaIni = $this->xml($p['dateIni'] . 'T00:00:00');
        $fechaFin = $this->xml($p['dateFin'] . 'T00:00:00');
        $adults = $this->xml($p['adults']);
        $rooms = $this->xml($p['numHabs']);
        $pass = $this->xml($fc['pass']);
        $cx = $this->xml($fc['cx']);

        $xml = <<<XML
        <?xml version="1.0" encoding="utf-8"?>
        <soap12:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap12="http://www.w3.org/2003/05/soap-envelope">
          <soap12:Body>
            <GetHabitacionTarifasFechasFotoDescrip_ES_EN xmlns="https://fcsistemas.com/">
              <lFechaIni>{$fechaIni}</lFechaIni>
              <lFechaFinal>{$fechaFin}</lFechaFinal>
              <lAdul>{$adults}</lAdul>
              <lMen>0</lMen>
              <lJr>0</lJr>
              <lHabs>{$rooms}</lHabs>
              <lPassCliente>{$pass}</lPassCliente>
              <lStringCxSAHM>{$cx}</lStringCxSAHM>
            </GetHabitacionTarifasFechasFotoDescrip_ES_EN>
          </soap12:Body>
        </soap12:Envelope>
        XML;

        $endpoint = $fc['soap_endpoint'];
        $timeout = $this->availabilitySoapTimeout($fc, $multipleHotels, $hotelCode);

        if (filter_var($fc['soap_debug'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
            Log::info('SOAP tarifas consulta', [
                'hotel_code' => $hotelCode,
                'endpoint' => $endpoint,
                'timeout' => $timeout,
                'dateIni' => $p['dateIni'] ?? null,
                'dateFin' => $p['dateFin'] ?? null,
                'adults' => $p['adults'] ?? null,
                'numHabs' => $p['numHabs'] ?? null,
            ]);
        }

        $resp = Http::connectTimeout(min(5, $timeout))
            ->timeout($timeout)
            ->withHeaders([
                'Content-Type' => 'application/soap+xml; charset=utf-8',
            ])
            ->withBody($xml, 'application/soap+xml; charset=utf-8')
            ->post($endpoint);

        if (!$resp->ok()) {
            throw new \RuntimeException('SOAP tarifas error: ' . $resp->status() . ' ' . $resp->body());
        }

        return $resp->body();
    }

    /**
     * Parsea TODO el XML de tarifas, devolviendo un arreglo de habitaciones:
     * [
     *   [
     *     code, name, plan, images[], rates[] [{date, rate}], nights_total, sum_rate, sum_rate_cents
     *   ],
     *   ...
     * ]
     */
    private function parseTarifasAll(string $xmlBody, string $hotelCode): array
    {
        $doc = new \DOMDocument();
        $doc->loadXML($xmlBody);
        $xp = new \DOMXPath($doc);

        // Selecciona todos los nodos de habitación sin depender de namespaces
        $roomNodes = $xp->evaluate(
            "//*[local-name()='GetHabitacionTarifasFechasFotoDescrip_ES_ENResult']" .
                "/*[local-name()='cTarifaHabitacionFotoFechaDescrip_ES_EN']"
        );

        $rooms = [];
        $txt = function (\DOMNode $ctx, string $name) use ($xp) {
            return trim((string)$xp->evaluate("string(./*[local-name()='{$name}'])", $ctx));
        };

        /** @var \DOMElement $roomNode */
        foreach ($roomNodes as $roomNode) {
            $images = array_values(array_filter([
                $txt($roomNode, 'Img1'),
                $txt($roomNode, 'Img2'),
                $txt($roomNode, 'Img3'),
            ]));

            $plan = trim((string)$xp->evaluate("string(.//*[local-name()='TipoPlan'][1])", $roomNode));

            $rates = [];
            foreach ($xp->evaluate(".//*[local-name()='FechaLista']/*[local-name()='cFechaTarifa']", $roomNode) as $ft) {
                /** @var \DOMNode $ft */
                $fecha  = trim((string)$xp->evaluate("string(./*[local-name()='Fecha'])", $ft));
                $tarifa = (float)trim((string)$xp->evaluate("string(./*[local-name()='TarifaFecha'])", $ft));
                $rates[] = [
                    'date' => $fecha,   // "YYYY-MM-DDTHH:mm:ss"
                    'rate' => $tarifa,  // unidad tal como la devuelve la API
                ];
            }

            $nightsTotal   = count($rates);
            $sumRate       = array_sum(array_column($rates, 'rate'));
            $sumRateCents  = (int) round($sumRate * 100);

            $rooms[] = [
                'hotel_code'      => $hotelCode,
                'hotel_name'      => HotelConfig::name($hotelCode),
                'code'            => $txt($roomNode, 'Tipo_Hab'),
                'name'            => $txt($roomNode, 'Descripcion_HabitacionCorta') ?: $txt($roomNode, 'Descripcion_HabitacionEN'),
                'plan'            => $plan,
                'images'          => $images,
                'rates'           => $rates,
                'nights_total'    => $nightsTotal,
                'sum_rate'        => $sumRate,
                'sum_rate_cents'  => $sumRateCents,
            ];
        }

        return $rooms;
    }

    private function renderAvailability(Request $request, array $hotelCodes, array $viewConfig)
    {
        $data = [];
        $hotelGroups = [];
        $availabilityError = null;

        if ($request->filled(['dateIni', 'dateFin', 'numHabs', 'adults'])) {
            $this->extendAvailabilityTimeLimit($hotelCodes);

            $data = $request->only(['dateIni', 'dateFin', 'typeHab', 'numHabs', 'adults']);

            $data['dateIni'] = Carbon::parse($data['dateIni'])->format('Y-m-d');
            $data['dateFin'] = Carbon::parse($data['dateFin'])->format('Y-m-d');
            $data['adults'] = (int) $data['adults'];
            $data['numHabs'] = (int) $data['numHabs'];

            foreach ($hotelCodes as $hotelCode) {
                $rooms = [];

                try {
                    $tarifasXml = $this->callSoapTarifasFechas($data, $hotelCode, count($hotelCodes) > 1);
                    $rooms = $this->parseTarifasAll($tarifasXml, $hotelCode);

                    $fc = HotelConfig::fc($hotelCode);
                    if (filter_var($fc['soap_debug'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
                        Log::info('SOAP tarifas OK', [
                            'hotel_code' => $hotelCode,
                            'room_count' => count($rooms),
                            'rooms' => array_map(fn ($room) => [
                                'code' => $room['code'] ?? null,
                                'name' => $room['name'] ?? null,
                            ], $rooms),
                        ]);
                    }
                } catch (\Throwable $e) {
                    Log::warning('SOAP tarifas fallo', [
                        'hotel_code' => $hotelCode,
                        'msg' => $e->getMessage(),
                    ]);

                    $availabilityError = 'Hubo un error al intentar traer las habitaciones disponibles. Por favor intenta de nuevo en unos minutos.';
                }

                $hotelGroups[] = [
                    'code' => $hotelCode,
                    'name' => HotelConfig::name($hotelCode),
                    'rooms' => $rooms,
                ];
            }
        } else {
            $hotelGroups = array_map(function (string $hotelCode) {
                return [
                    'code' => $hotelCode,
                    'name' => HotelConfig::name($hotelCode),
                    'rooms' => [],
                ];
            }, $hotelCodes);
        }

        $automaticPromotion = null;
        if (!empty($data['dateIni']) && !empty($data['dateFin'])) {
            $previewHotelCode = count($hotelCodes) === 1 ? $hotelCodes[0] : null;
            $automaticPromotion = app(PricingService::class)
                ->activePromotionPreview($data['dateIni'], $data['dateFin'], null, $previewHotelCode);
        }

        return Inertia::render('Disponibilidad', [
            'data' => $data,
            'hotelGroups' => $hotelGroups,
            'automaticPromotion' => $automaticPromotion,
            'searchPath' => $viewConfig['search_path'],
            'pageTitle' => $viewConfig['page_title'],
            'heroTitle' => $viewConfig['hero_title'],
            'heroBadge' => $viewConfig['hero_badge'],
            'heroImage' => $viewConfig['hero_image'] ?? '/img/home-1.webp',
            'useParrasBranding' => (bool) ($viewConfig['use_parras_branding'] ?? false),
            'isSingleHotel' => count($hotelCodes) === 1,
            'availabilityError' => $availabilityError,
        ]);
    }

    private function validateAvailabilityRequest(Request $request): array
    {
        $data = $request->validate([
            'dateIni'  => ['required', 'date'],
            'dateFin'  => ['required', 'date', 'after_or_equal:dateIni'],
            'numHabs'  => ['required', 'integer', 'min:1'],
            'adults'   => ['required', 'integer', 'min:1'],
        ], [
            'dateIni.required' => 'La fecha inicial es obligatoria',
            'dateFin.required' => 'La fecha final es obligatoria',
            'numHabs.required' => 'El número de habitaciones es obligatorio',
            'adults.required'  => 'La cantidad de adultos es obligatoria',
        ]);

        $data['dateIni'] = Carbon::parse($data['dateIni'])->format('Y-m-d');
        $data['dateFin'] = Carbon::parse($data['dateFin'])->format('Y-m-d');

        return $data;
    }

    private function xml($s): string
    {
        return htmlspecialchars((string) $s, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    private function availabilitySoapTimeout(array $fc, bool $multipleHotels, ?string $hotelCode = null): int
    {
        $configured = (int) ($fc['availability_soap_timeout'] ?? $fc['soap_timeout'] ?? 10);
        $maxForSearch = $multipleHotels ? 10 : ($hotelCode === 'parras' ? 60 : 20);

        return max(1, min($configured, $maxForSearch));
    }

    private function extendAvailabilityTimeLimit(array $hotelCodes): void
    {
        $limit = 30;

        foreach ($hotelCodes as $hotelCode) {
            $fc = HotelConfig::fc($hotelCode);
            $limit = max($limit, (int) ($fc['availability_request_time_limit'] ?? 90));
        }

        if (function_exists('set_time_limit')) {
            @set_time_limit($limit);
        }
    }
}
