<?php

namespace App\Services;

class PricingService
{
    public function __construct(
        private readonly GlobalPromotionService $promotions,
        private readonly CuponService $coupons,
    ) {
    }

    public function activePromotionPreview($checkin, $checkout, ?string $roomTypeCode = null, ?string $hotelCode = null): ?array
    {
        return $this->promotions->presentPromotion(
            $this->promotions->resolveActivePromotion($checkin, $checkout, $roomTypeCode, $hotelCode)
        );
    }

    public function buildPricingData(array $data): array
    {
        $subtotalCents = max(0, (int) ($data['amount'] ?? $data['subtotal_cents'] ?? $data['amount_cents'] ?? 0));
        $roomTypeCode = $data['room_type_code'] ?? $data['room_code'] ?? null;
        $hotelCode = $data['hotel_code'] ?? null;

        $promotion = $this->promotions->resolveActivePromotion(
            $data['checkin'] ?? $data['check_in'] ?? null,
            $data['checkout'] ?? $data['check_out'] ?? null,
            $roomTypeCode,
            $hotelCode
        );

        $promotionDiscountCents = 0;
        $promotionData = null;
        $subtotalAfterPromotionCents = $subtotalCents;

        if ($promotion) {
            $promotionDiscountCents = $this->promotions->calculateDiscountCents(
                $promotion->discount_type,
                (float) $promotion->discount_value,
                $subtotalCents
            );
            $subtotalAfterPromotionCents = max(0, $subtotalCents - $promotionDiscountCents);
            $promotionData = array_merge($this->promotions->presentPromotion($promotion), [
                'subtotal_cents' => $subtotalCents,
                'discount_cents' => $promotionDiscountCents,
                'total_cents' => $subtotalAfterPromotionCents,
            ]);
        }

        $couponCode = $data['coupon_code'] ?? null;
        $couponData = null;
        $finalCents = $subtotalAfterPromotionCents;

        if (filled($couponCode)) {
            $couponData = $this->coupons->buildDiscountData($couponCode, $subtotalAfterPromotionCents);
            $finalCents = (int) $couponData['total_cents'];
            unset($couponData['coupon']);
            $couponData['consumed_at'] = null;
        }

        $meta = [
            'pricing' => [
                'subtotal_cents' => $subtotalCents,
                'promotion_discount_cents' => $promotionDiscountCents,
                'coupon_discount_cents' => (int) ($couponData['discount_cents'] ?? 0),
                'final_cents' => $finalCents,
            ],
            'promotion' => $promotionData,
            'coupon' => $couponData,
        ];

        return [
            'promotion' => $promotion,
            'promotion_data' => $promotionData,
            'coupon' => $couponData,
            'final_cents' => $finalCents,
            'meta' => $meta,
        ];
    }
}
