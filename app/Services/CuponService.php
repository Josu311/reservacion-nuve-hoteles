<?php

namespace App\Services;

use App\Models\CuponCode;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CuponService
{
    public function validateCoupon(?string $code): CuponCode
    {
        $code = $this->normalizeCode($code);

        if ($code === '') {
            throw ValidationException::withMessages([
                'coupon_code' => 'Ingresa un codigo de descuento.',
            ]);
        }

        $coupon = CuponCode::query()
            ->whereRaw('UPPER(code) = ?', [$code])
            ->first();

        if (!$coupon) {
            throw ValidationException::withMessages([
                'coupon_code' => 'El codigo de descuento no existe.',
            ]);
        }

        if (!$coupon->status) {
            throw ValidationException::withMessages([
                'coupon_code' => 'El codigo de descuento no esta activo.',
            ]);
        }

        $now = now();
        if ($coupon->starts_at && $coupon->starts_at->greaterThan($now)) {
            throw ValidationException::withMessages([
                'coupon_code' => 'El codigo de descuento aun no esta disponible.',
            ]);
        }

        if ($coupon->expires_at && $coupon->expires_at->lessThan($now)) {
            throw ValidationException::withMessages([
                'coupon_code' => 'El codigo de descuento expiro.',
            ]);
        }

        if ($coupon->usage_limit !== null && $coupon->times_used >= $coupon->usage_limit) {
            throw ValidationException::withMessages([
                'coupon_code' => 'El codigo de descuento ya alcanzo su limite de usos.',
            ]);
        }

        return $coupon;
    }

    public function buildDiscountData(?string $code, int $subtotalCents): array
    {
        $coupon = $this->validateCoupon($code);
        $subtotalCents = max(0, $subtotalCents);
        $discountCents = $this->calculateDiscountCents(
            $coupon->discount_type,
            (float) $coupon->discount_value,
            $subtotalCents
        );

        return [
            'coupon' => $coupon,
            'code' => strtoupper($coupon->code),
            'discount_type' => $coupon->discount_type,
            'discount_value' => (float) $coupon->discount_value,
            'subtotal_cents' => $subtotalCents,
            'discount_cents' => $discountCents,
            'total_cents' => max(0, $subtotalCents - $discountCents),
        ];
    }

    public function consumeCoupon(string $code): ?CuponCode
    {
        $normalized = $this->normalizeCode($code);

        return DB::transaction(function () use ($normalized) {
            $coupon = CuponCode::query()
                ->whereRaw('UPPER(code) = ?', [$normalized])
                ->lockForUpdate()
                ->first();

            if (!$coupon) {
                return null;
            }

            $coupon->increment('times_used');

            return $coupon->refresh();
        });
    }

    public function calculateDiscountCents(?string $type, float $value, int $subtotalCents): int
    {
        $subtotalCents = max(0, $subtotalCents);

        if ($subtotalCents === 0 || $value <= 0) {
            return 0;
        }

        $discountCents = match ($type) {
            'percentage' => (int) round($subtotalCents * ($value / 100)),
            'fixed' => (int) round($value * 100),
            default => 0,
        };

        return max(0, min($subtotalCents, $discountCents));
    }

    private function normalizeCode(?string $code): string
    {
        return strtoupper(trim((string) $code));
    }
}
