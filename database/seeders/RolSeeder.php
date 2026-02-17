<?php

namespace Database\Seeders;

use App\Models\Rol;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RolSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $seeders = [
            'usuario',
            'admin',
            'superadmin'
        ];

        foreach ($seeders as $seeder) {
            Rol::create([
                'name' => $seeder
            ]);
        }
    }
}
