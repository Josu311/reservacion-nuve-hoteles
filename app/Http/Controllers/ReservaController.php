<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ReservaController extends Controller
{
    /**
     * GET /disponibilidad
     * Renderiza la página con TODAS las habitaciones y sus tarifas por fecha.
     */
    public function index(Request $request)
    {
        if($request->only(['dateIni', 'dateFin', 'typeHab', 'numHabs', 'adults'])) {
            // 1) Normaliza/asegura filtros
            $data = $request->only(['dateIni', 'dateFin', 'typeHab', 'numHabs', 'adults']);
    
            // Defaults sensatos si llegan vacíos (evita errores en acceso directo)
            $start = $data['dateIni'];
            $end   = $data['dateFin'];
    
            $data['dateIni'] = Carbon::parse($start)->format('Y-m-d');
            $data['dateFin'] = Carbon::parse($end)->format('Y-m-d');
            $data['adults']  = (int)($data['adults']);
            $data['numHabs'] = (int)($data['numHabs']);
    
            // 2) SOAP: tarifas por fecha (todas las habitaciones)
            try {
                $tarifasXml = $this->callSoapTarifasFechas([
                    'lFechaIni'     => $data['dateIni'] . 'T00:00:00',
                    'lFechaFinal'   => $data['dateFin'] . 'T00:00:00',
                    'lAdul'         => $data['adults'],
                    'lMen'          => 0,
                    'lJr'           => 0,
                    'lHabs'         => $data['numHabs'],
                    'lPassCliente'  => config('services.fc.pass'),
                    'lStringCxSAHM' => config('services.fc.cx'),
                ]);
    
                $rooms = $this->parseTarifasAll($tarifasXml);
            } catch (\Throwable $e) {
                Log::warning('SOAP tarifas fallo', ['msg' => $e->getMessage()]);
                $rooms = [];
            }
        }

        // 3) Render: manda TODO al front
        return Inertia::render('Disponibilidad', [
            'data'  => $data ?? [],   // filtros normalizados
            'rooms' => $rooms ?? [],  // arreglo de habitaciones con tarifas/imágenes/plan
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
    private function callSoapTarifasFechas(array $p): string
    {
        $xml = <<<XML
        <?xml version="1.0" encoding="utf-8"?>
        <soap12:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap12="http://www.w3.org/2003/05/soap-envelope">
          <soap12:Body>
            <GetHabitacionTarifasFechasFotoDescrip_ES_EN xmlns="https://fcsistemas.com/">
              <lFechaIni>{$p['lFechaIni']}</lFechaIni>
              <lFechaFinal>{$p['lFechaFinal']}</lFechaFinal>
              <lAdul>{$p['lAdul']}</lAdul>
              <lMen>{$p['lMen']}</lMen>
              <lJr>{$p['lJr']}</lJr>
              <lHabs>{$p['lHabs']}</lHabs>
              <lPassCliente>{$p['lPassCliente']}</lPassCliente>
              <lStringCxSAHM>{$p['lStringCxSAHM']}</lStringCxSAHM>
            </GetHabitacionTarifasFechasFotoDescrip_ES_EN>
          </soap12:Body>
        </soap12:Envelope>
        XML;

        $endpoint = config('services.fc.soap_endpoint');
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
    private function parseTarifasAll(string $xmlBody): array
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
}
