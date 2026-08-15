<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\MarketingSource;

class MarketingSourceSeeder extends Seeder
{
    public function run(): void
    {
        $sources = ['Google', 'Instagram / Facebook', 'Indicação de Amigo', 'Passou na Frente da Loja', 'Panfleto', 'Já era cliente'];
        foreach ($sources as $source) {
            MarketingSource::create(['tenant_id' => 1, 'name' => $source]);
        }
    }
}