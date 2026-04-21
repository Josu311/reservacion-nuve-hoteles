<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class FcSistemasClient
{
    public function disponibilidadTipo(string $roomTypeCode, $checkin, $checkout, int $rooms, ?string $hotelCode = null): bool
    {
        $hotelCode = HotelConfig::normalize($hotelCode);
        $fc = HotelConfig::fc($hotelCode);
        $endpoint = $fc['soap_endpoint'] ?? null;
        $action   = 'https://fcsistemas.com/fDisponibilidadTipo';
        $pass     = $fc['pass'] ?? null;
        $cx       = $fc['cx'] ?? null;

        if (!$endpoint || !$pass || !$cx) {
            return false;
        }

        // SOAP pide dateTime. Normalmente se manda inicio de día.
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

        $resp = Http::retry(2, 300)
            ->timeout(20)
            ->withHeaders([
                'Content-Type' => 'application/soap+xml; charset=utf-8; action="' . $action . '"',
            ])
            ->withBody($xml, 'application/soap+xml; charset=utf-8')
            ->post($endpoint);

        if (!$resp->ok()) {
            return false;
        }

        // Esperas: <fDisponibilidadTipoResult>DISPONIBILIDAD</fDisponibilidadTipoResult>
        $result = $this->parseSoapString($resp->body(), 'fDisponibilidadTipoResult');

        return strtoupper(trim($result ?? '')) === 'DISPONIBILIDAD';
    }

    private function parseSoapString(string $xmlBody, string $nodeName): ?string
    {
        $doc = new \DOMDocument();
        $doc->loadXML($xmlBody);

        $xp = new \DOMXPath($doc);
        $value = trim((string)$xp->evaluate('string(//*[local-name()="' . $nodeName . '"])'));

        return $value !== '' ? $value : null;
    }

    private function iso($d): string
    {
        return Carbon::parse($d)->format('Y-m-d\TH:i:s');
    }

    private function xml($s): string
    {
        return htmlspecialchars((string)$s, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
