<?php

namespace Database\Seeders;

use App\Models\DeviceType;
use Illuminate\Database\Seeder;

class DeviceTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            'Celular',
            'Notebook',
            'TV',
            'Tablet',
            'Computador'
        ];

        foreach ($types as $type) {
            DeviceType::create([
                'tenant_id' => 1, // Vinculado à sua assistência
                'name' => $type,
            ]);
        }
    }
}