<?php

namespace App\Filament\Resources\ServiceOrders\Schemas;

// --- COMPONENTES DE ESTRUTURA E LAYOUT (Schemas) ---
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs;         
use Filament\Schemas\Components\Tabs\Tab;     
use Filament\Schemas\Schema;

// --- COMPONENTES DE PREENCHIMENTO E VISUAIS (Forms) ---
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;     
use Filament\Forms\Components\RichEditor;     
use Filament\Support\RawJs;
use Illuminate\Support\Facades\Http;

use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\Device;
use App\Models\DeviceType;
use App\Models\DeviceBrand;
use App\Models\ServiceModality;

class ServiceOrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->schema([
                Tabs::make('Painel da OS')
                    ->tabs([
                        // ==========================================
                        // ABA 1: RECEPÇÃO (Sempre visível)
                        // ==========================================
                        Tab::make('1. Recepção do Aparelho')
                            ->icon('heroicon-m-inbox-arrow-down')
                            ->schema([
                                
                                // SECTION 1: IDENTIFICAÇÃO DO CLIENTE E ATENDIMENTO
                                Section::make('1. Identificação e Atendimento')
                                    ->columns(2)
                                    ->schema([
                                        Select::make('customer_id')
                                            ->label('Pesquisar Cliente')
                                            ->relationship('customer', 'name')
                                            ->searchable(['name', 'document', 'phone', 'email'])
                                            ->placeholder('Digite o Nome, CPF ou Telefone...')
                                            ->preload(false)
                                            ->live()
                                            // REGRA NOVA: Permite CRIAR um cliente direto pela OS
                                            ->createOptionForm([
                                                TextInput::make('name')->label('Nome Completo')->required(),
                                                TextInput::make('document')->label('CPF / CNPJ')
                                                    ->mask(RawJs::make(<<<'JS'
                                                        $input.length > 14 ? '99.999.999/9999-99' : '999.999.999-99'
                                                    JS)),
                                                TextInput::make('phone')->label('Telefone / WhatsApp')->tel()
                                                    ->mask(RawJs::make(<<<'JS'
                                                        $input.length >= 15 ? '(99) 99999-9999' : '(99) 9999-9999'
                                                    JS)),
                                                TextInput::make('email')->label('E-mail')->email(),
                                            ])
                                            ->createOptionUsing(function (array $data) {
                                                $data['tenant_id'] = auth()->user()->tenant_id ?? 1;
                                                if(isset($data['document'])) {
                                                    $somenteNumeros = preg_replace('/[^0-9]/', '', $data['document']);
                                                    $data['document'] = $somenteNumeros;
                                                    $data['customer_type'] = strlen($somenteNumeros) > 11 ? 'PJ' : 'PF';
                                                }
                                                if(isset($data['phone'])) {
                                                    $data['phone'] = preg_replace('/[^0-9]/', '', $data['phone']);
                                                }
                                                $customer = Customer::create($data);
                                                return $customer->id;
                                            })
                                            ->afterStateUpdated(function ($set, ?string $state) {
                                                if ($state) {
                                                    $cliente = Customer::find($state);
                                                    $set('customer_name', $cliente->name);
                                                    $set('customer_document', $cliente->document); 
                                                    $set('customer_phone', $cliente->phone);
                                                    $set('customer_email', $cliente->email);
                                                } else {
                                                    $set('customer_name', null);
                                                    $set('customer_document', null);
                                                    $set('customer_phone', null);
                                                    $set('customer_email', null);
                                                }
                                                $set('device_id', null);
                                                $set('customer_address_id', null);
                                            })
                                            ->columnSpanFull()
                                            ->hidden(fn (string $operation) => $operation === 'edit'),

                                        Select::make('service_modality_id')
                                            ->label('Modalidade de Atendimento')
                                            ->relationship('modality', 'name')
                                            ->required()
                                            ->live(),

                                        DateTimePicker::make('scheduled_at')
                                            ->label('Data e Hora (Agendamento)')
                                            ->seconds(false)
                                            ->displayFormat('d/m/Y H:i')
                                            ->visible(function ($get) {
                                                $modality = ServiceModality::find($get('service_modality_id'));
                                                return $modality?->requires_scheduling ?? false;
                                            })
                                            ->required(function ($get) {
                                                $modality = ServiceModality::find($get('service_modality_id'));
                                                return $modality?->requires_scheduling ?? false;
                                            }),

                                        Select::make('customer_address_id')
                                            ->label('Endereço para Coleta / Visita')
                                            ->placeholder('Selecione ou cadastre um endereço...')
                                            ->options(function ($get) {
                                                $customerId = $get('customer_id');
                                                if (!$customerId) return [];
                                                
                                                return CustomerAddress::where('customer_id', $customerId)
                                                    ->get()
                                                    ->mapWithKeys(fn ($addr) => [
                                                        $addr->id => "{$addr->street}, {$addr->number} - {$addr->neighborhood}"
                                                    ]);
                                            })
                                            // REGRA NOVA: Permite CRIAR um endereço direto pela OS
                                            ->createOptionForm([
                                                Grid::make(3)->schema([
                                                    TextInput::make('zip_code')
                                                        ->label('CEP')
                                                        ->mask('99999-999')
                                                        ->live(onBlur: true)
                                                        ->afterStateUpdated(function ($set, ?string $state) {
                                                            $cep = preg_replace('/[^0-9]/', '', (string)$state);
                                                            if (strlen($cep) !== 8) return;
                                                            $response = Http::get("https://viacep.com.br/ws/{$cep}/json/");
                                                            if ($response->successful() && !$response->json('erro')) {
                                                                $data = $response->json();
                                                                $set('street', $data['logradouro'] ?? null);
                                                                $set('neighborhood', $data['bairro'] ?? null);
                                                                $set('city', $data['localidade'] ?? null);
                                                                $set('state', $data['uf'] ?? null);
                                                            }
                                                        }),
                                                    TextInput::make('street')->label('Rua/Avenida')->required()->columnSpan(2),
                                                ]),
                                                Grid::make(3)->schema([
                                                    TextInput::make('number')->label('Número')->required(),
                                                    TextInput::make('complement')->label('Complemento'),
                                                    TextInput::make('neighborhood')->label('Bairro')->required(),
                                                ]),
                                                Grid::make(2)->schema([
                                                    TextInput::make('city')->label('Cidade')->required(),
                                                    TextInput::make('state')->label('Estado (UF)')->length(2)->required(),
                                                ]),
                                            ])
                                            ->createOptionUsing(function (array $data, $get) {
                                                $data['customer_id'] = $get('customer_id');
                                                $data['tenant_id'] = auth()->user()->tenant_id ?? 1;
                                                if(isset($data['zip_code'])){
                                                    $data['zip_code'] = preg_replace('/[^0-9]/', '', $data['zip_code']);
                                                }
                                                $address = CustomerAddress::create($data);
                                                return $address->id;
                                            })
                                            ->visible(fn ($get) => $get('customer_id') !== null)
                                            ->columnSpanFull()
                                            ->required(function ($get) {
                                                $modality = ServiceModality::find($get('service_modality_id'));
                                                return $modality?->requires_scheduling ?? false;
                                            }),
                                            
                                        // DADOS ESPELHADOS DO CLIENTE (Apenas leitura)
                                        TextInput::make('customer_name')
                                            ->label('Nome Completo')
                                            ->disabled(fn (string $operation) => $operation === 'edit')
                                            ->formatStateUsing(fn ($record, $state) => $record ? $record->customer?->name : $state)
                                            ->hidden(fn (string $operation) => $operation === 'create'),
                                    ]),

                                // SECTION 2: DADOS DO EQUIPAMENTO
                                Section::make('2. Dados do Equipamento')
                                    ->columns(3)
                                    ->schema([
                                        Select::make('device_id')
                                            ->label('Aparelhos deste Cliente')
                                            ->options(fn ($get) => Device::where('customer_id', $get('customer_id'))->pluck('model', 'id'))
                                            ->placeholder('Novo Aparelho...')
                                            ->live()
                                            ->afterStateUpdated(function ($set, ?string $state) {
                                                if ($state) {
                                                    $device = Device::find($state);
                                                    $set('device_type_id', $device->device_type_id);
                                                    $set('device_brand_id', $device->device_brand_id);
                                                    $set('device_model', $device->model);
                                                    $set('device_serial_number', $device->serial_number);
                                                    $set('color', $device->color); 
                                                }
                                            })
                                            ->columnSpanFull()
                                            ->hidden(fn (string $operation, $get) => $operation === 'edit' || ! $get('customer_id')),

                                        Select::make('device_type_id')
                                            ->label('Tipo')
                                            ->options(DeviceType::pluck('name', 'id'))
                                            ->required(fn (string $operation) => $operation === 'create')
                                            ->searchable()
                                            ->disabled(fn (string $operation) => $operation === 'edit')
                                            ->formatStateUsing(fn ($record, $state) => $record ? $record->device?->device_type_id : $state),

                                        Select::make('device_brand_id')
                                            ->label('Marca')
                                            ->options(DeviceBrand::pluck('name', 'id'))
                                            ->required(fn (string $operation) => $operation === 'create')
                                            ->searchable()
                                            ->disabled(fn (string $operation) => $operation === 'edit')
                                            ->formatStateUsing(fn ($record, $state) => $record ? $record->device?->device_brand_id : $state),

                                        TextInput::make('device_model')
                                            ->label('Modelo')
                                            ->required()
                                            ->formatStateUsing(fn ($record, $state) => $record ? $record->device?->model : $state),
                                        
                                        TextInput::make('device_serial_number')
                                            ->label('Número de Série')
                                            ->formatStateUsing(fn ($record, $state) => $record ? $record->device?->serial_number : $state),
                                            
                                        TextInput::make('color')
                                            ->label('Cor do Aparelho')
                                            ->placeholder('Ex: Preto')
                                            ->nullable()
                                            ->formatStateUsing(fn ($record, $state) => $record ? $record->device?->color : $state),
                                    ]),

                                // SECTION 3: TRIAGEM E LAUDO DE ENTRADA (MUDANÇA AQUI)
                                Section::make('3. Triagem e Laudo de Entrada')
                                    ->columns(1) // O segredo para empilhar: coluna única!
                                    ->schema([
                                        Select::make('service_order_status_id')
                                            ->label('Status Inicial')
                                            ->relationship('status', 'name')
                                            ->default(1)
                                            ->required(),

                                        Textarea::make('customer_report')
                                            ->label('Relato do Cliente')
                                            ->placeholder('O que o cliente relatou que está acontecendo...')
                                            ->required()
                                            ->rows(3),

                                        Textarea::make('initial_symptom')
                                            ->label('Defeito Constatado (Teste de Recepção)')
                                            ->placeholder('O que o técnico constatou na bancada de entrada...')
                                            ->required()
                                            ->rows(3),

                                        Textarea::make('equipment_condition')
                                            ->label('Estado Físico do Aparelho')
                                            ->placeholder('Registrar riscos, telas trincadas, amassados...')
                                            ->rows(2),

                                        Textarea::make('general_observations')
                                            ->label('Observações Adicionais')
                                            ->placeholder('Informações extras relevantes para a OS...')
                                            ->rows(2),
                                    ]),
                            ]), 

                        // ==========================================
                        // ABA 2: LABORATÓRIO (Oculta na criação)
                        // ==========================================
                        Tab::make('2. Laboratório e Diagnóstico')
                            ->icon('heroicon-m-wrench-screwdriver')
                            ->hidden(fn (string $operation): bool => $operation === 'create')
                            ->schema([
                                RichEditor::make('technical_diagnostic')
                                    ->label('Laudo Técnico (Diagnóstico Detalhado)')
                                    ->toolbarButtons(['bold', 'italic', 'bulletList', 'orderedList', 'undo', 'redo'])
                                    ->columnSpanFull(),
                                
                                Textarea::make('solution')
                                    ->label('Solução Aplicada / Serviços Executados')
                                    ->rows(4)
                                    ->columnSpanFull(),
                            ]),

                        // ==========================================
                        // ABA 3: COMERCIAL / FINANCEIRO (Oculta na criação)
                        // ==========================================
                        Tab::make('3. Orçamento e Encerramento')
                            ->icon('heroicon-m-currency-dollar')
                            ->hidden(fn (string $operation): bool => $operation === 'create')
                            ->columns(2)
                            ->schema([
                                TextInput::make('estimated_cost')
                                    ->label('Custo Estimado (Orçamento)')
                                    ->numeric()
                                    ->prefix('R$'),

                                TextInput::make('final_value')
                                    ->label('Valor Final (Cobrado)')
                                    ->numeric()
                                    ->prefix('R$'),

                                DatePicker::make('exit_date')
                                    ->label('Data de Saída / Entrega')
                                    ->displayFormat('d/m/Y'),

                                Textarea::make('output_notes')
                                    ->label('Observações de Saída (Termos de Garantia)')
                                    ->rows(3)
                                    ->columnSpanFull(),
                            ]),
                    ]),
            ]);
    }
}