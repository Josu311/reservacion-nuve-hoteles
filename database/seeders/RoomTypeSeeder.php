<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoomTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();

        DB::table('room_types')->insert([
            [
                'code'              => '1K',
                'name'              => 'King size',
                'max_occupancy'     => 2,
                'base_rate_cents'   => 220000, // $2,200.00 MXN
                'currency'          => 'MXN',
                'beds_json'         => json_encode([['type' => 'king', 'count' => 1]]),
                'amenities_json'    => json_encode(['wifi', 'ac', 'tv', 'parking']),
                'active'            => true,
                'created_at'        => $now,
                'updated_at'        => $now,
            ],
            [
                'code'              => '1M',
                'name'              => 'Habitación sencilla',
                'max_occupancy'     => 2,
                'base_rate_cents'   => 180000, // $1,800.00 MXN
                'currency'          => 'MXN',
                'beds_json'         => json_encode([['type' => 'double', 'count' => 1]]),
                'amenities_json'    => json_encode(['wifi', 'ac', 'tv', 'parking']),
                'active'            => true,
                'created_at'        => $now,
                'updated_at'        => $now,
            ],
            [
                'code'              => '2M',
                'name'              => 'Habitación doble',
                'max_occupancy'     => 4,
                'base_rate_cents'   => 200000, // $2,000.00 MXN
                'currency'          => 'MXN',
                'beds_json'         => json_encode([['type' => 'double', 'count' => 2]]),
                'amenities_json'    => json_encode(['wifi', 'ac', 'tv', 'parking']),
                'active'            => true,
                'created_at'        => $now,
                'updated_at'        => $now,
            ],
            [
                'code'              => 'S-',
                'name'              => 'Sencilla',
                'max_occupancy'     => 2,
                'base_rate_cents'   => 115000, // $1,150.00 MXN
                'currency'          => 'MXN',
                'beds_json'         => json_encode([['type' => 'double', 'count' => 1]]),
                'amenities_json'    => json_encode(['wifi', 'ac', 'tv', 'parking', 'breakfast']),
                'active'            => true,
                'created_at'        => $now,
                'updated_at'        => $now,
            ],
            [
                'code'              => 'SS',
                'name'              => 'Superior sencilla',
                'max_occupancy'     => 2,
                'base_rate_cents'   => 115000, // $1,150.00 MXN
                'currency'          => 'MXN',
                'beds_json'         => json_encode([['type' => 'double', 'count' => 1]]),
                'amenities_json'    => json_encode(['wifi', 'ac', 'tv', 'parking', 'breakfast']),
                'active'            => true,
                'created_at'        => $now,
                'updated_at'        => $now,
            ],
            [
                'code'              => 'D-',
                'name'              => 'Doble',
                'max_occupancy'     => 4,
                'base_rate_cents'   => 120000, // $1,200.00 MXN
                'currency'          => 'MXN',
                'beds_json'         => json_encode([['type' => 'double', 'count' => 2]]),
                'amenities_json'    => json_encode(['wifi', 'ac', 'tv', 'parking', 'breakfast']),
                'active'            => true,
                'created_at'        => $now,
                'updated_at'        => $now,
            ],
            [
                'code'              => 'DS',
                'name'              => 'Superior doble',
                'max_occupancy'     => 4,
                'base_rate_cents'   => 125000, // $1,250.00 MXN
                'currency'          => 'MXN',
                'beds_json'         => json_encode([['type' => 'double', 'count' => 2]]),
                'amenities_json'    => json_encode(['wifi', 'ac', 'tv', 'parking', 'breakfast']),
                'active'            => true,
                'created_at'        => $now,
                'updated_at'        => $now,
            ],
            [
                'code'              => 'DO',
                'name'              => 'Doble',
                'max_occupancy'     => 4,
                'base_rate_cents'   => 180000, // $1,800.00
                'currency'          => 'MXN',
                'beds_json'         => json_encode([['type' => 'double', 'count' => 2]]),
                'amenities_json'    => json_encode(['wifi', 'ac', 'tv']),
                'active'            => true,
                'created_at'        => $now,
                'updated_at'        => $now,
            ],
            [
                'code'              => 'MK',
                'name'              => 'Queen',
                'max_occupancy'     => 2,
                'base_rate_cents'   => 200000, // $2,000.00
                'currency'          => 'MXN',
                'beds_json'         => json_encode([['type' => 'queen', 'count' => 1]]),
                'amenities_json'    => json_encode(['wifi', 'ac', 'tv']),
                'active'            => true,
                'created_at'        => $now,
                'updated_at'        => $now,
            ],
            [
                'code'              => 'SD',
                'name'              => 'Standard',
                'max_occupancy'     => 3,
                'base_rate_cents'   => 250000, // $2,500.00
                'currency'          => 'MXN',
                'beds_json'         => json_encode([['type' => 'king', 'count' => 1], ['type' => 'single', 'count' => 1]]),
                'amenities_json'    => json_encode(['wifi', 'ac', 'tv']),
                'active'            => true,
                'created_at'        => $now,
                'updated_at'        => $now,
            ],
            [
                'code'              => 'SK',
                'name'              => 'Standard King Size',
                'max_occupancy'     => 4,
                'base_rate_cents'   => 350000, // $3,500.00
                'currency'          => 'MXN',
                'beds_json'         => json_encode([['type' => 'king', 'count' => 1], ['type' => 'sofa', 'count' => 1]]),
                'amenities_json'    => json_encode(['wifi', 'ac', 'tv', 'minibar', 'tina']),
                'active'            => true,
                'created_at'        => $now,
                'updated_at'        => $now,
            ],
        ]);
    }
}
