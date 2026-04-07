<?php

namespace App\Filament\Resources\ServiceOrders\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class ServiceOrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('OS #')
                    ->sortable(),
                TextColumn::make('customer.name')
                    ->label('Cliente')
                    ->searchable(),
                TextColumn::make('device.model')
                    ->label('Aparelho')
                    ->searchable(),
                TextColumn::make('status.name')
                    ->label('Status')
                    ->badge(),
                TextColumn::make('entry_date')
                    ->label('Data de Entrada')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}