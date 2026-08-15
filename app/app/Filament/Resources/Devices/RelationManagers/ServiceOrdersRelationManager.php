<?php

namespace App\Filament\Resources\Devices\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\Action; 

class ServiceOrdersRelationManager extends RelationManager
{
    protected static string $relationship = 'serviceOrders';
    protected static ?string $title = 'Histórico de Ordens de Serviço';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('OS N°')
                    ->sortable(),
                    
                TextColumn::make('status.name')
                    ->label('Status')
                    ->badge(),
                    
                // Coluna compactada que exibe Relato + Defeito
                TextColumn::make('customer_report')
                    ->label('Relato do Cliente')
                    ->default('Sem relato registrado') // Fallback se estiver nulo
                    ->description(fn ($record) => 'Defeito / Balcão: ' . ($record->initial_symptom ?: 'Não testado'))
                    ->limit(50)
                    ->wrap(),
                    
                TextColumn::make('created_at')
                    ->label('Entrada')
                    ->dateTime('d/m/Y H:i'),
            ])
            ->actions([
                Action::make('Abrir OS')
                    ->icon('heroicon-m-arrow-top-right-on-square')
                    ->url(fn ($record) => "/app/luciano-tech/service-orders/{$record->id}/edit"),
            ]);
    }
}