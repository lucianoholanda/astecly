<?php

namespace App\Filament\Resources\Devices\Pages;

use App\Filament\Resources\Devices\DeviceResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use Filament\Actions;

class ListDevices extends ListRecords
{
    protected static string $resource = DeviceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            // Filtra aparelhos que possuem uma Ordem de Serviço em aberto (sem data de saída)
            'na_loja' => Tab::make('Aparelhos na Loja')
                ->icon('heroicon-m-building-storefront')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereHas('serviceOrders', function ($q) {
                    $q->whereNull('exit_date');
                })),
                
            // Exibe o histórico de todos os aparelhos já registrados no sistema
            'todos' => Tab::make('Todos os Registros')
                ->icon('heroicon-m-list-bullet'),
        ];
    }
}