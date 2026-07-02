<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class TenantSeeder extends Seeder
{
    public function run(): void
    {
        Tenant::withoutEvents(function () {
            
            // 1. Cria o Tenant (A conta no sistema) - ADICIONADO O '$tenant ='
            $tenant = Tenant::create([
                'id' => 1,
                'slug' => 'luciano-tech',
                'plan_type' => 'premium',
                'is_active' => true,
            ]);
            
            // 2. Cria o Perfil da Empresa vinculada a este Tenant
            Company::create([
                'tenant_id' => $tenant->id, // Agora a variável $tenant existe e funciona!
                'company_name' => 'Luciano Assistência Técnica',
                'trading_name' => 'Luh Tech Repair',
                'document' => '00.000.000/0001-00',
                'phone' => '(00) 90000-0000',
                'general_conditions' => '1. O orçamento tem validade de 7 dias. 2. Aparelhos não retirados em 90 dias serão descartados ou vendidos para custear o serviço.',
                'warranty_terms' => 'Garantia de 90 dias sobre o serviço executado e peças trocadas, não cobrindo mau uso, quedas ou contato com líquidos.',
            ]);
        });
    }
}