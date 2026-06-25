<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ServiceModality;

class ServiceModalitySeeder extends Seeder
{
    public function run(): void
    {
        $modalities = [
            [
                'tenant_id' => 1,
                'name' => 'Balcão',
                'price' => 0.00,
                'requires_scheduling' => false,
            ],
            [
                'tenant_id' => 1,
                'name' => 'Coleta (Até 55")',
                'price' => 80.00,
                'requires_scheduling' => true,
            ],
            [
                'tenant_id' => 1,
                'name' => 'Visita Técnica (Até 75")',
                'price' => 250.00,
                'requires_scheduling' => true,
            ],
            [
                'tenant_id' => 1,
                'name' => 'Visita Técnica (Acima de 75")',
                'price' => 350.00,
                'requires_scheduling' => true,
            ],
        ];

        foreach ($modalities as $modality) {
            ServiceModality::create($modality);
        }
    }
}