<?php

namespace App\Services;

use App\Models\GlobalPromotion;
use Carbon\Carbon;

class GlobalPromotionService
{
    public function resolveActivePromotion($checkin, $checkout, ?string $roomTypeCode = null, ?string $hotelCode = null): ?GlobalPromotion
    {
        if (!$checkin || !$checkout) {
            return null;
        }

        $now = now();
        $checkinDate = Carbon::parse($checkin)->toDateString();
        $checkoutDate = Carbon::parse($checkout)->toDateString();
        $roomTypeCode = $roomTypeCode ? strtoupper(trim($roomTypeCode)) : null;
        $hotelCode = $hotelCode ? strtolower(trim($hotelCode)) : null;

        return GlobalPromotion::query()
            ->where('status', true)
            ->where(function ($query) use ($now) {
                $query->whereNull('booking_starts_at')
                    ->orWhere('booking_starts_at', '<=', $now);
            })
            ->where(function ($query) use ($now) {
                $query->whereNull('booking_ends_at')
                    ->orWhere('booking_ends_at', '>=', $now);
            })
            ->where(function ($query) use ($checkinDate) {
                $query->whereNull('stay_starts_at')
                    ->orWhere('stay_starts_at', '<=', $checkinDate);
            })
            ->where(function ($query) use ($checkoutDate) {
                $query->whereNull('stay_ends_at')
                    ->orWhere('stay_ends_at', '>=', $checkoutDate);
            })
            ->when($hotelCode, function ($query) use ($hotelCode) {
                $query->where(function ($inner) use ($hotelCode) {
                    $inner->whereNull('hotel_code')
                        ->orWhere('hotel_code', $hotelCode);
                });
            }, function ($query) {
                $query->whereNull('hotel_code');
            })
            ->when($roomTypeCode, function ($query) use ($roomTypeCode) {
                $query->where(function ($inner) use ($roomTypeCode) {
                    $inner->whereNull('room_type_code')
                        ->orWhereRaw('UPPER(room_type_code) = ?', [$roomTypeCode]);
                });
            }, function ($query) {
                $query->whereNull('room_type_code');
            })
            ->orderByDesc('priority')
            ->orderByDesc('id')
            ->first();
    }

    public function presentPromotion(?GlobalPromotion $promotion): ?array
    {
        if (!$promotion) {
            return null;
        }

        return [
            'id' => $promotion->id,
            'name' => $promotion->name,
            'description' => $promotion->description,
            'discount_type' => $promotion->discount_type,
            'discount_value' => (float) $promotion->discount_value,
            'hotel_code' => $promotion->hotel_code,
            'room_type_code' => $promotion->room_type_code,
            'priority' => (int) $promotion->priority,
        ];
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
}
