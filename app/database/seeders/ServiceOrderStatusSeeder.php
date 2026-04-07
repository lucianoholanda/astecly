<?php

namespace Database\Seeders;

use App\Models\ServiceOrderStatus;
use Illuminate\Database\Seeder;

class ServiceOrderStatusSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            ['name' => 'Orçamento Pendente', 'color' => 'warning', 'is_default' => true],
            ['name' => 'Aprovado - Aguardando Peça', 'color' => 'info', 'is_default' => false],
            ['name' => 'Em Manutenção na Bancada', 'color' => 'primary', 'is_default' => false],
            ['name' => 'Aguardando Retirada', 'color' => 'success', 'is_default' => false],
            ['name' => 'Entregue / Finalizado', 'color' => 'gray', 'is_default' => false],
            ['name' => 'Orçamento Recusado', 'color' => 'danger', 'is_default' => false],
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