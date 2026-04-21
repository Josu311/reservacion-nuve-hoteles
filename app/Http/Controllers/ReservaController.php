<?php

namespace App\Http\Controllers;

use App\Services\HotelConfig;
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
        $data = [];
        $rooms = [];
        $roomsGomez = [];

        if ($request->filled(['dateIni', 'dateFin', 'numHabs', 'adults'])) {
            // 1) Normaliza/asegura filtros
            $data = $request->only(['dateIni', 'dateFin', 'typeHab', 'numHabs', 'adults']);

            // Defaults sensatos si llegan vacíos (evita errores en acceso directo)
            $start = $data['dateIni'];
            $end   = $data['dateFin'];

            $data['dateIni'] = Carbon::parse($start)->format('Y-m-d');
            $data['dateFin'] = Carbon::parse($end)->format('Y-m-d');
            $data['adults']  = (int)($data['adults']);
            $data['numHabs'] = (int)($data['numHabs']);

            // 2) SOAP: tarifas por fecha por hotel.
            try {
                $tarifasXml = $this->callSoapTarifasFechas($data, 'torreon');
                $rooms = $this->parseTarifasAll($tarifasXml, 'torreon');
            } catch (\Throwable $e) {
                Log::warning('SOAP tarifas fallo', [
                    'hotel_code' => 'torreon',
                    'msg' => $e->getMessage(),
                ]);
                $rooms = [];
            }

            try {
                $tarifasXmlGomez = $this->callSoapTarifasFechas($data, 'gomez');
                $roomsGomez = $this->parseTarifasAll($tarifasXmlGomez, 'gomez');
            } catch (\Throwable $e) {
                Log::warning('SOAP tarifas fallo', [
                    'hotel_code' => 'gomez',
                    'msg' => $e->getMessage(),
                ]);
                $roomsGomez = [];
            }
        }

        // 3) Render: manda TODO al front
        return Inertia::render('Disponibilidad', [
            'data'  => $data,   // filtros normalizados
            'rooms' => $rooms,  // arreglo de habitaciones con tarifas/imágenes/plan
            'roomsGomez' => $roomsGomez,  // arreglo de habitaciones con tarifas/imágenes/plan
        ]);
    }

    /**
     * POST /disponibilidad
     * Valida el formulario y redirige con query string al GET.
     */
    public function validateDataUserHabs(Request $request)
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

        // Normaliza a YYYY-MM-DD
        $data['dateIni'] = Carbon::parse($data['dateIni'])->format('Y-m-d');
        $data['dateFin'] = Carbon::parse($data['dateFin'])->format('Y-m-d');

        // Redirige al GET con query string
        return to_route('disponibilidad.index', $data);
    }

    /**
     * Llama al método SOAP GetHabitacionTarifasFechasFotoDescrip_ES_EN (todas las habitaciones).
     * Devuelve el XML de la respuesta o lanza excepción en error.
     */
    private function callSoapTarifasFechas(array $p, string $hotelCode): string
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
        $action   = 'https://fcsistemas.com/GetHabitacionTarifasFechasFotoDescrip_ES_EN';

        $resp = Http::retry(2, 300)
            ->timeout(20)
            ->withHeaders([
                'Content-Type' => 'application/soap+xml; charset=utf-8; action="' . $action . '"',
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

    private function xml($s): string
    {
        return htmlspecialchars((string) $s, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
