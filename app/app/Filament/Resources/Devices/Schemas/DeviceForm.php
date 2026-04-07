<?php

namespace App\Filament\Resources\Devices\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class DeviceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('tenant_id')
                    ->relationship('tenant', 'id')
                    ->required(),
                Select::make('customer_id')
                    ->relationship('customer', 'name')
                    ->required(),
                TextInput::make('device_type_id')
                    ->required()
                    ->numeric(),
                TextInput::make('device_brand_id')
                    ->required()
                    ->numeric(),
                TextInput::make('model')
                    ->default(null),
                TextInput::make('color')
                    ->default(null),
                TextInput::make('serial_number')
                    ->default(null),
                Textarea::make('device_condition')
                    ->default(null)
                    ->columnSpanFull(),
                Textarea::make('photos')
                    ->default(null)
                    ->columnSpanFull(),
            ]);
    }
}
