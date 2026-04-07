<?php

namespace App\Filament\Resources\ServiceOrderStatuses\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ServiceOrderStatusForm
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
                TextInput::make('color')
                    ->default(null),
                Toggle::make('is_default')
                    ->required(),
            ]);
    }
}
