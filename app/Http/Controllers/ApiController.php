<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ApiController extends Controller
{
    public function getStates()
    {
        $endpoint = 'http://fcsistemas.ddns.net:8092/wsSAHM2011.asmx';
        $connectionString = env('FC_SOAP_CX', 'HotelNuveTorreonCxString');

        $xmlBody = <<<XML
        <?xml version="1.0" encoding="utf-8"?>
        <soap12:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap12="http://www.w3.org/2003/05/soap-envelope">
        <soap12:Body>
            <GetPaisRegiones xmlns="https://fcsistemas.com/">
            <lPaisId>MX</lPaisId>
            <lPassCliente>enwk@difs@uwoi</lPassCliente>
            <lStringCxSAHM>{$connectionString}</lStringCxSAHM>
            </GetPaisRegiones>
        </soap12:Body>
        </soap12:Envelope>
        XML;

        $response = Http::withHeaders([
            'Content-Type' => 'application/soap+xml; charset=utf-8',
        ])->withBody($xmlBody, 'application/soap+xml')
            ->post($endpoint);

        if (! $response->ok()) {
            // Error real de la petición SOAP
            return response()->json([
                'success' => false,
                'status'  => $response->status(),
                'body'    => $response->body(),
            ], 500);
        }

        // ---- XML de la respuesta SOAP ----
        $xmlString = trim($response->body());

        // ===============================
        // 1) Intentar parsear con SimpleXML
        // ===============================
        libxml_use_internal_errors(true);

        $xml = simplexml_load_string($xmlString, 'SimpleXMLElement', LIBXML_NOCDATA);

        if ($xml !== false) {
            // Namespaces
            $xml->registerXPathNamespace('soap', 'http://www.w3.org/2003/05/soap-envelope');
            $xml->registerXPathNamespace('ns', 'https://fcsistemas.com/');

            // Buscar los nodos de regiones
            $regions = $xml->xpath('//ns:GetPaisRegionesResult/ns:cPaisRegion');

            if ($regions && count($regions)) {
                $data = [];

                foreach ($regions as $region) {
                    $data[] = [
                        'id'          => (string) $region->Id_Region,
                        'description' => (string) $region->Descripcion_Region,
                    ];
                }

                return response()->json([
                    'success' => true,
                    'source'  => 'simplexml',
                    'count'   => count($data),
                    'data'    => $data,
                ]);
            }
            // Si no encontró nodos, caemos al fallback de abajo
        }

        // ===============================
        // 2) Fallback: parsear con regex
        // ===============================
        $pattern = '/<cPaisRegion>\s*'
            . '<Descripcion_Region>([^<]*)<\/Descripcion_Region>\s*'
            . '<Id_Region>([^<]*)<\/Id_Region>\s*'
            . '<\/cPaisRegion>/i';

        $matches = [];
        preg_match_all($pattern, $xmlString, $matches, PREG_SET_ORDER);

        if (!empty($matches)) {
            $data = [];

            foreach ($matches as $m) {
                $data[] = [
                    'description' => $m[1],
                    'id'          => $m[2],
                ];
            }

            return response()->json([
                'success' => true,
                'source'  => 'regex_fallback',
                'count'   => count($data),
                'data'    => $data,
                // 'debug_raw_xml' => $xmlString, // si quieres verlo, descomenta
            ]);
        }

        // ===============================
        // 3) Si de plano nada funcionó
        // ===============================
        $errors = libxml_get_errors();
        $messages = [];

        foreach ($errors as $error) {
            $messages[] = trim($error->message);
        }
        libxml_clear_errors();

        return response()->json([
            'success'      => false,
            'message'      => 'No se pudo procesar la respuesta SOAP',
            'parseErrors'  => $messages,
            'raw_xml'      => $xmlString,
        ], 500);
    }

    public function getCities(Request $request)
    {
        // 1) Leer la región del request
        $regionId = $request->input('region_id'); // 👈 aquí esperas { "region_id": 3224 }

        if (!$regionId) {
            return response()->json([
                'success' => false,
                'message' => 'El campo region_id es obligatorio',
            ], 422);
        }

        $endpoint = 'http://fcsistemas.ddns.net:8092/wsSAHM2011.asmx';
        $connectionString = env('FC_SOAP_CX', 'HotelNuveTorreonCxString');

        // 2) Armar el body SOAP con la región
        $xmlBody = <<<XML
        <?xml version="1.0" encoding="utf-8"?>
        <soap12:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap12="http://www.w3.org/2003/05/soap-envelope">
        <soap12:Body>
            <GetPaisRegionCiudades xmlns="https://fcsistemas.com/">
            <lRegionId>{$regionId}</lRegionId>
            <lPassCliente>enwk@difs@uwoi</lPassCliente>
            <lStringCxSAHM>{$connectionString}</lStringCxSAHM>
            </GetPaisRegionCiudades>
        </soap12:Body>
        </soap12:Envelope>
        XML;

        // 3) Petición HTTP al servicio SOAP
        $response = Http::withHeaders([
            'Content-Type' => 'application/soap+xml; charset=utf-8',
        ])->withBody($xmlBody, 'application/soap+xml')
            ->post($endpoint);

        if (!$response->ok()) {
            return response()->json([
                'success' => false,
                'status'  => $response->status(),
                'body'    => $response->body(),
            ], 500);
        }

        $xmlString = trim($response->body());

        // 4) Parsear el XML
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($xmlString, 'SimpleXMLElement', LIBXML_NOCDATA);

        if ($xml === false) {
            $errors = libxml_get_errors();
            $messages = [];

            foreach ($errors as $error) {
                $messages[] = trim($error->message);
            }
            libxml_clear_errors();

            return response()->json([
                'success'     => false,
                'message'     => 'No se pudo parsear el XML de GetPaisRegionCiudades',
                'parseErrors' => $messages,
                'raw_xml'     => $xmlString,
            ], 500);
        }

        // Namespaces según tu XML de ejemplo
        $xml->registerXPathNamespace('soap', 'http://www.w3.org/2003/05/soap-envelope');
        $xml->registerXPathNamespace('ns', 'https://fcsistemas.com/');

        // 5) Obtener todos los <cPaisRegionCiudad>
        $nodes = $xml->xpath('//ns:GetPaisRegionCiudadesResult/ns:cPaisRegionCiudad');

        if (!$nodes || !count($nodes)) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontraron ciudades en la respuesta',
                'raw_xml' => $xmlString,
            ], 500);
        }

        // 6) Convertirlos a un arreglo plano para el front
        $data = [];

        foreach ($nodes as $city) {
            $data[] = [
                'id'          => (string) $city->Id_Ciudad,
                'description' => (string) $city->Descripcion_Ciudad,
            ];
        }

        return response()->json([
            'success' => true,
            'region_id' => (string) $regionId,
            'count'   => count($data),
            'data'    => $data,
        ]);
    }

    public function getAddress(Request $request)
    {
        // 1) Leer el CP del request
        $cp = $request->input('cp');

        if (!$cp) {
            return response()->json([
                'success' => false,
                'message' => 'El campo cp es obligatorio',
            ], 422);
        }

        // Opcional: si quieres validar que sean 5 dígitos:
        if (!preg_match('/^\d{5}$/', $cp)) {
            return response()->json([
                'success' => false,
                'message' => 'El CP debe tener 5 dígitos numéricos',
            ], 422);
        }

        $endpoint = 'http://fcsistemas.ddns.net:8092/wsSAHM2011.asmx';
        $connectionString = env('FC_SOAP_CX', 'HotelNuveTorreonCxString');

        // 2) Body SOAP usando el CP del request
        $xmlBody = <<<XML
        <?xml version="1.0" encoding="utf-8"?>
        <soap12:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap12="http://www.w3.org/2003/05/soap-envelope">
        <soap12:Body>
            <GetPaisRegionCiudadCP xmlns="https://fcsistemas.com/">
            <lPaisId>MX</lPaisId>
            <lCP>{$cp}</lCP>
            <lPassCliente>enwk@difs@uwoi</lPassCliente>
            <lStringCxSAHM>{$connectionString}</lStringCxSAHM>
            </GetPaisRegionCiudadCP>
        </soap12:Body>
        </soap12:Envelope>
        XML;

        // 3) Petición SOAP
        $response = Http::withHeaders([
            'Content-Type' => 'application/soap+xml; charset=utf-8',
        ])->withBody($xmlBody, 'application/soap+xml')
            ->post($endpoint);

        if (! $response->ok()) {
            return response()->json([
                'success' => false,
                'status'  => $response->status(),
                'body'    => $response->body(),
            ], 500);
        }

        $xmlString = trim($response->body());

        // 4) Parsear XML
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($xmlString, 'SimpleXMLElement', LIBXML_NOCDATA);

        if ($xml === false) {
            $errors = libxml_get_errors();
            $messages = [];

            foreach ($errors as $error) {
                $messages[] = trim($error->message);
            }
            libxml_clear_errors();

            return response()->json([
                'success'     => false,
                'message'     => 'No se pudo parsear el XML de GetPaisRegionCiudadCP',
                'parseErrors' => $messages,
                'raw_xml'     => $xmlString,
            ], 500);
        }

        // Namespaces según la respuesta que mandaste
        $xml->registerXPathNamespace('soap', 'http://www.w3.org/2003/05/soap-envelope');
        $xml->registerXPathNamespace('ns', 'https://fcsistemas.com/');

        // 5) Buscar el nodo <cPaisRegionCiudadCP>
        $nodes = $xml->xpath('//ns:GetPaisRegionCiudadCPResult/ns:cPaisRegionCiudadCP');

        if (!$nodes || !isset($nodes[0])) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró el nodo cPaisRegionCiudadCP en la respuesta',
                'raw_xml' => $xmlString,
            ], 500);
        }

        $node = $nodes[0];

        $regionId = (string) $node->Id_Region;
        $cityId   = (string) $node->Id_Ciudad;

        // 6) Responder en JSON como quieres
        return response()->json([
            'success'    => true,
            'region_id'  => $regionId,
            'city_id'    => $cityId,
        ]);
    }
}
