<?php

namespace Database\Seeders;

use App\Models\ServiceOrderStatus;
use Illuminate\Database\Seeder;

class ServiceOrderStatusSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            ['name' => 'Em Análise', 'color' => 'warning', 'is_default' => true],
            ['name' => 'Aguardando Coleta', 'color' => 'warning', 'is_default' => false],
            ['name' => 'Aguardando Visita', 'color' => 'warning', 'is_default' => false],
            ['name' => 'Aguardando Peça', 'color' => 'info', 'is_default' => false],
            ['name' => 'Em Manutenção', 'color' => 'primary', 'is_default' => false],
            ['name' => 'Aguardando Retirada', 'color' => 'success', 'is_default' => false],
            ['name' => 'Retirado', 'color' => 'gray', 'is_default' => false],
            ['name' => 'Entregue', 'color' => 'gray', 'is_default' => false],
            ['name' => 'Não Aprovado', 'color' => 'danger', 'is_default' => false],
            ['name' => 'Condenado', 'color' => 'danger', 'is_default' => false],
            ['name' => 'Descarte', 'color' => 'danger', 'is_default' => false],
        ];

        foreach ($statuses as $status) {
            ServiceOrderStatus::create([
                'tenant_id' => 1,
                'name' => $status['name'],
                'color' => $status['color'],
                'is_default' => $status['is_default'],
            ]);
        }
    }
}