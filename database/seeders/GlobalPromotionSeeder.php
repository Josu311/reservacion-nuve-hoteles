<?php

namespace Database\Seeders;

use App\Models\GlobalPromotion;
use Illuminate\Database\Seeder;

class GlobalPromotionSeeder extends Seeder
{
    public function run(): void
    {
        $year = now()->year;

        GlobalPromotion::updateOrCreate(
            ['name' => "Promocion Mayo {$year}"],
            [
                'description' => 'Promocion automatica de ejemplo para estancias en mayo.',
                'status' => true,
                'discount_type' => 'percentage',
                'discount_value' => 15,
                'booking_starts_at' => now()->subDay(),
                'booking_ends_at' => now()->addMonths(6),
                'stay_starts_at' => "{$year}-05-01",
                'stay_ends_at' => "{$year}-05-31",
                'hotel_code' => null,
                'room_type_code' => null,
                'priority' => 100,
            ]
        );
    }
}
