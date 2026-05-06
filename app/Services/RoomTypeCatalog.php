<?php

namespace App\Services;

class RoomTypeCatalog
{
    /**
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            '1K' => 'King size',
            '1M' => 'Habitación sencilla',
            '2M' => 'Habitación doble',
            'S-' => 'Sencilla',
            'SS' => 'Superior sencilla',
            'D-' => 'Doble',
            'DS' => 'Superior doble',
            'DO' => 'Doble',
            'MK' => 'Queen',
            'SD' => 'Standard',
            'SK' => 'Standard King Size',
        ];
    }

    public static function label(?string $code): string
    {
        $normalizedCode = strtoupper(trim((string) $code));

        if ($normalizedCode === '') {
            return 'No disponible';
        }

        return static::labels()[$normalizedCode] ?? $normalizedCode;
    }
}
