<?php

namespace App\Filament\Resources\ServiceOrders\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use App\Models\Customer;
use App\Models\Device;
use App\Models\DeviceType;
use App\Models\DeviceBrand;

class ServiceOrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->schema([
                Section::make('1. Identificação do Cliente')
                    ->description('Busque pelo nome, CPF/CNPJ ou telefone, ou preencha para cadastrar um novo.')
                    ->columns(2)
                    ->schema([
                        Select::make('customer_id')
                            ->label('Pesquisar Cliente')
                            ->relationship('customer', 'name')
                            // Adicionado 'document' (CPF/CNPJ) na busca
                            ->searchable(['name', 'document', 'phone', 'email'])
                            ->placeholder('Novo Cliente...')
                            ->preload()
                            ->live()
                            // CORREÇÃO: Removido a tipagem 'Set' antes de $set
                            ->afterStateUpdated(function ($set, ?string $state) {
                                if ($state) {
                                    $cliente = Customer::find($state);
                                    $set('customer_name', $cliente->name);
                                    $set('customer_document', $cliente->document); // Busca o CPF/CNPJ
                                    $set('customer_phone', $cliente->phone);
                                    $set('customer_email', $cliente->email);
                                } else {
                                    $set('customer_name', null);
                                    $set('customer_document', null);
                                    $set('customer_phone', null);
                                    $set('customer_email', null);
                                }
                                $set('device_id', null);
                            })
                            ->columnSpanFull(),

                        TextInput::make('customer_name')
                            ->label('Nome Completo')
                            ->required(),

                        TextInput::make('customer_document')
                            ->label('CPF / CNPJ'),

                        TextInput::make('customer_phone')
                            ->label('Telefone / WhatsApp'),

                        TextInput::make('customer_email')
                            ->label('E-mail')
                            ->email(),
                    ]),

                Section::make('2. Dados do Equipamento')
                    ->columns(3)
                    ->schema([
                        Select::make('device_id')
                            ->label('Aparelhos deste Cliente')
                            // CORREÇÃO: Removido a tipagem 'Get' antes de $get
                            ->options(fn ($get) => Device::where('customer_id', $get('customer_id'))->pluck('model', 'id'))
                            ->placeholder('Novo Aparelho...')
                            ->live()
                            ->afterStateUpdated(function ($set, ?string $state) {
                                if ($state) {
                                    $device = Device::find($state);
                                    $set('device_type_id', $device->device_type_id);
                                    $set('device_brand_id', $device->device_brand_id);
                                    $set('device_model', $device->model);
                                }
                            })
                            ->columnSpanFull()
                            ->hidden(fn ($get) => ! $get('customer_id')),

                        Select::make('device_type_id')
                            ->label('Tipo')
                            ->options(DeviceType::pluck('name', 'id'))
                            ->required()
                            ->preload()
                            ->searchable(),

                        Select::make('device_brand_id')
                            ->label('Marca')
                            ->options(DeviceBrand::pluck('name', 'id'))
                            ->required()
                            ->preload()
                            ->searchable(),

                        TextInput::make('device_model')
                            ->label('Modelo / Versão')
                            ->required(),
                    ]),

                Section::make('3. Detalhes da Entrada')
                    ->columns(2)
                    ->schema([
                        Select::make('service_order_status_id')
                            ->label('Status da OS')
                            ->relationship('status', 'name')
                            ->default(1)
                            ->required()
                            ->columnSpanFull(),

                        Textarea::make('reported_defect')
                            ->label('Defeito Relatado')
                            ->required()
                            ->rows(3),

                        Textarea::make('input_notes')
                            ->label('Observações (Acessórios, Riscos, etc)')
                            ->rows(3),
                    ]),
            ]);
    }
}