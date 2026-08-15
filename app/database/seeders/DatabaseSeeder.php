<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            TenantSeeder::class,
            UserSeeder::class,
            ServiceOrderStatusSeeder::class,
            DeviceTypeSeeder::class,
            DeviceBrandSeeder::class,
            ServiceModalitySeeder::class,
            MarketingSourceSeeder::class,
        ]);
    }
}