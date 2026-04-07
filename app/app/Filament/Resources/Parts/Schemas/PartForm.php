<?php

namespace App\Filament\Resources\Parts\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class PartForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('tenant_id')
                    ->required()
                    ->numeric(),
                TextInput::make('name')
                    ->required(),
                TextInput::make('sku')
                    ->label('SKU')
                    ->default(null),
                TextInput::make('purchase_price')
                    ->required()
                    ->numeric()
                    ->default(0.0)
                    ->prefix('$'),
                TextInput::make('freight_cost')
                    ->required()
                    ->numeric()
                    ->default(0.0)
                    ->prefix('$'),
                TextInput::make('freight_type')
                    ->default(null),
                TextInput::make('origin')
                    ->default(null),
                TextInput::make('sale_price')
                    ->required()
                    ->numeric()
                    ->default(0.0)
                    ->prefix('$'),
                TextInput::make('stock_quantity')
                    ->required()
                    ->numeric()
                    ->default(0),
                Textarea::make('photos')
                    ->default(null)
                    ->columnSpanFull(),
            ]);
    }
}
