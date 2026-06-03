<?php

namespace Database\Seeders;

use App\Models\CuponCode;
use Illuminate\Database\Seeder;

class CuponCodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        CuponCode::updateOrCreate(
            ['code' => 'PRUEBA10'],
            [
                'discount_type' => 'percentage',
                'discount_value' => 10,
                'usage_limit' => 100,
                'times_used' => 0,
                'status' => true,
                'starts_at' => now()->subDay(),
                'expires_at' => now()->addMonths(6),
            ]
        );
    }
}
