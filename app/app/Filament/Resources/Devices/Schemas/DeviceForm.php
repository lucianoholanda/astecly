<?php

namespace App\Filament\Resources\Devices\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Actions;
use Filament\Actions\Action;
use Filament\Schemas\Schema;

class DeviceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1) // Força o layout global a ter apenas 1 coluna
            ->components([
                Hidden::make('tenant_id')->default(fn () => auth()->user()->tenant_id),
                
                // --- BLOCO 1: CLIENTE ---
                Section::make('Informações do Cliente')
                    ->schema([
                        Select::make('customer_id')
                            ->label('Selecionar Cliente')
                            ->relationship('customer', 'name')
                            ->required()
                            ->searchable()
                            ->hidden(fn ($record) => $record !== null),
                        
                        // Campos injetados manualmente e não salvos no banco (dehydrated = false)
                        TextInput::make('cliente_nome')
                            ->label('Nome')
                            ->formatStateUsing(fn ($record) => $record?->customer?->name)
                            ->disabled()
                            ->dehydrated(false)
                            ->visible(fn ($record) => $record !== null),
                            
                        TextInput::make('cliente_cpf')
                            ->label('CPF')
                            ->formatStateUsing(fn ($record) => $record?->customer?->cpf)
                            ->mask('999.999.999-99')
                            ->disabled()
                            ->dehydrated(false)
                            ->visible(fn ($record) => $record !== null),
                            
                        TextInput::make('cliente_telefone')
                            ->label('Telefone')
                            ->formatStateUsing(fn ($record) => $record?->customer?->phone)
                            ->mask('(99) 99999-9999')
                            ->disabled()
                            ->dehydrated(false)
                            ->visible(fn ($record) => $record !== null),
                            
                        TextInput::make('cliente_email')
                            ->label('E-mail')
                            ->formatStateUsing(fn ($record) => $record?->customer?->email)
                            ->disabled()
                            ->dehydrated(false)
                            ->visible(fn ($record) => $record !== null),
                            
                        Actions::make([
                            Action::make('Ver Cliente')
                                ->icon('heroicon-m-user')
                                ->color('warning')
                                ->url(fn ($record) => "/app/luciano-tech/customers/{$record->customer_id}/edit")
                                ->button(),
                        ])->visible(fn ($record) => $record !== null),
                    ])
                    ->columns(1) // Um campo debaixo do outro
                    ->columnSpanFull(), // O bloco ocupa 100% da tela

                // --- BLOCO 2: APARELHO ---
                Section::make('Detalhes do Aparelho')
                    ->schema([
                        Select::make('device_type_id')
                            ->label('Tipo de Aparelho')
                            ->relationship('type', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        
                        Select::make('device_brand_id')
                            ->label('Marca')
                            ->relationship('brand', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),

                        TextInput::make('model')->label('Modelo'),
                        TextInput::make('serial_number')->label('Número de Série'),
                        TextInput::make('color')->label('Cor'),
                    ])
                    ->columns(2) // Estes campos ficam 2x2 para economizar espaço
                    ->columnSpanFull(), // O bloco ocupa 100% da tela

                // --- BLOCO 3: ESTADO ---
                Section::make('Avaliação Inicial')
                    ->schema([
                        Textarea::make('device_condition')
                            ->label('Condição do Aparelho (Arranhões, marcas de uso, etc)'),
                    ])
                    ->columnSpanFull(), // O bloco ocupa 100% da tela
            ]);
    }
}