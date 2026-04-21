<?php

namespace App\Services;

use InvalidArgumentException;

class HotelConfig
{
    public const DEFAULT_CODE = 'torreon';

    public static function codes(): array
    {
        return array_keys(self::all());
    }

    public static function normalize(?string $hotelCode): string
    {
        $hotelCode = strtolower(trim((string) ($hotelCode ?: self::DEFAULT_CODE)));

        if (!array_key_exists($hotelCode, self::all())) {
            throw new InvalidArgumentException("Hotel no configurado: {$hotelCode}");
        }

        return $hotelCode;
    }

    public static function get(?string $hotelCode): array
    {
        return self::all()[self::normalize($hotelCode)] ?? [];
    }

    public static function name(?string $hotelCode): string
    {
        return self::get($hotelCode)['name'] ?? self::normalize($hotelCode);
    }

    public static function fc(?string $hotelCode): array
    {
        return self::get($hotelCode)['fc'] ?? [];
    }

    public static function stripe(?string $hotelCode): array
    {
        return self::get($hotelCode)['stripe'] ?? [];
    }

    public static function stripeSecret(?string $hotelCode): string
    {
        $secret = self::stripe($hotelCode)['secret'] ?? null;

        if (!$secret) {
            throw new InvalidArgumentException('Falta configurar la clave secreta de Stripe para ' . self::name($hotelCode));
        }

        return $secret;
    }

    public static function stripeWebhookSecret(?string $hotelCode): string
    {
        $secret = self::stripe($hotelCode)['webhook_secret'] ?? null;

        if (!$secret) {
            throw new InvalidArgumentException('Falta configurar el webhook secret de Stripe para ' . self::name($hotelCode));
        }

        return $secret;
    }

    private static function all(): array
    {
        $hotels = config('services.hotels');

        if (is_array($hotels) && $hotels !== []) {
            return $hotels;
        }

        $legacyFc = config('services.fc', []);
        $legacyStripe = config('services.stripe', []);

        return [
            'torreon' => [
                'name' => 'Nuve Torreon',
                'fc' => $legacyFc,
                'stripe' => $legacyStripe,
            ],
            'gomez' => [
                'name' => 'Nuve Gomez',
                'fc' => $legacyFc,
                'stripe' => $legacyStripe,
            ],
        ];
    }
}
