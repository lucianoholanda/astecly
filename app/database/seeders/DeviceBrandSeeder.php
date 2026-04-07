<?php

namespace Database\Seeders;

use App\Models\DeviceBrand;
use Illuminate\Database\Seeder;

class DeviceBrandSeeder extends Seeder
{
    public function run(): void
    {
        $brands = [
            // Gigantes / Multi-dispositivos
            'Samsung', 'Apple', 'LG',
            // Telefonia
            'Motorola', 'Xiaomi', 'Asus', 'Realme', 'Poco',
            // Informática
            'Dell', 'HP', 'Lenovo', 'Acer', 'Vaio', 'Positivo',
            // TVs e Eletrônicos
            'Philco', 'TCL', 'AOC', 'Toshiba', 'Panasonic', 'Philips'
        ];

        // Ordenar em ordem alfabética para o banco ficar organizado
        sort($brands);

        foreach ($brands as $brand) {
            DeviceBrand::create([
                'tenant_id' => 1,
                'name' => $brand,
            ]);
        }
    }
}