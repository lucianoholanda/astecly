<?php

namespace App\Filament\Resources\ServiceOrders\Schemas;

// --- COMPONENTES DE ESTRUTURA E LAYOUT (Schemas) ---
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs;         
use Filament\Schemas\Components\Tabs\Tab;     
use Filament\Schemas\Schema;

// --- COMPONENTES DE PREENCHIMENTO E VISUAIS (Forms) ---
use Filament\Forms\Components\Placeholder; 
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DatePicker;     
use Filament\Forms\Components\RichEditor;     

use Illuminate\Support\HtmlString;
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
                Tabs::make('Painel da OS')
                    ->tabs([
                        // ==========================================
                        // ABA 1: RECEPÇÃO (Sempre visível)
                        // ==========================================
                        Tab::make('1. Recepção do Aparelho')
                            ->icon('heroicon-m-inbox-arrow-down')
                            ->schema([
                                
                                // SECTION 1: IDENTIFICAÇÃO DO CLIENTE
                                Section::make('1. Identificação do Cliente')
                                    ->description(fn (string $operation) => $operation === 'create' 
                                        ? 'Busque pelo nome, CPF/CNPJ ou telefone, ou preencha para cadastrar um novo.' 
                                        : 'Dados do cliente vinculados a esta Ordem de Serviço.')
                                    ->columns(2)
                                    ->schema([
                                        Select::make('customer_id')
                                            ->label('Pesquisar Cliente')
                                            ->relationship('customer', 'name')
                                            ->searchable(['name', 'document', 'phone', 'email'])
                                            ->placeholder('Novo Cliente...')
                                            ->preload()
                                            ->live()
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
                                            })
                                            ->columnSpanFull()
                                            // REGRA DE UX 1: Esconde a barra de busca na tela de Editar
                                            ->hidden(fn (string $operation) => $operation === 'edit'),

                                        TextInput::make('customer_name')
                                            ->label('Nome Completo')
                                            ->required(fn (string $operation) => $operation === 'create')
                                            // REGRA DE UX 2: Bloqueia a edição dos dados do cliente na OS
                                            ->disabled(fn (string $operation) => $operation === 'edit')
                                            ->formatStateUsing(fn ($record, $state) => $record ? $record->customer?->name : $state),

                                        TextInput::make('customer_document')
                                            ->label('CPF / CNPJ')
                                            ->disabled(fn (string $operation) => $operation === 'edit')
                                            ->formatStateUsing(fn ($record, $state) => $record ? $record->customer?->document : $state),

                                        TextInput::make('customer_phone')
                                            ->label('Telefone / WhatsApp')
                                            ->disabled(fn (string $operation) => $operation === 'edit')
                                            ->formatStateUsing(fn ($record, $state) => $record ? $record->customer?->phone : $state),

                                        TextInput::make('customer_email')
                                            ->label('E-mail')
                                            ->email()
                                            ->disabled(fn (string $operation) => $operation === 'edit')
                                            ->formatStateUsing(fn ($record, $state) => $record ? $record->customer?->email : $state),
                                    ]),

                                // SECTION 2: DADOS DO EQUIPAMENTO
                                Section::make('2. Dados do Equipamento')
                                    ->description(fn (string $operation) => $operation === 'create'
                                        ? 'Selecione um aparelho existente ou preencha para cadastrar um novo.'
                                        : 'Dados do equipamento sob análise técnico.')
                                    ->columns(2)
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
                                                }
                                            })
                                            ->columnSpanFull()
                                            // REGRA DE UX 3: Esconde a busca de aparelhos na Edição
                                            ->hidden(fn (string $operation, $get) => $operation === 'edit' || ! $get('customer_id')),

                                        Select::make('device_type_id')
                                            ->label('Tipo')
                                            ->options(DeviceType::pluck('name', 'id'))
                                            ->required(fn (string $operation) => $operation === 'create')
                                            ->preload()
                                            ->searchable()
                                            // Tipo e Marca ficam travados na Edição para manter histórico limpo
                                            ->disabled(fn (string $operation) => $operation === 'edit')
                                            ->formatStateUsing(fn ($record, $state) => $record ? $record->device?->device_type_id : $state),

                                        Select::make('device_brand_id')
                                            ->label('Marca')
                                            ->options(DeviceBrand::pluck('name', 'id'))
                                            ->required(fn (string $operation) => $operation === 'create')
                                            ->preload()
                                            ->searchable()
                                            ->disabled(fn (string $operation) => $operation === 'edit')
                                            ->formatStateUsing(fn ($record, $state) => $record ? $record->device?->device_brand_id : $state),

                                        TextInput::make('device_model')
                                            ->label('Modelo / Versão')
                                            ->required()
                                            // Permite o técnico corrigir/editar o modelo se precisar
                                            ->formatStateUsing(fn ($record, $state) => $record ? $record->device?->model : $state),
                                        
                                        TextInput::make('device_serial_number')
                                            ->label('Número de Série / IMEI')
                                            // Permite o técnico preencher/corrigir o número de série na bancada
                                            ->formatStateUsing(fn ($record, $state) => $record ? $record->device?->serial_number : $state),
                                    ]),

                                // SECTION 3: DETALHES DA ENTRADA
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
                            ]), // Fim da Aba 1

                        // ==========================================
                        // ABA 2: LABORATÓRIO (Oculta na criação)
                        // ==========================================
                        Tab::make('2. Laboratório e Diagnóstico')
                            ->icon('heroicon-m-wrench-screwdriver')
                            ->hidden(fn (string $operation): bool => $operation === 'create')
                            ->schema([
                                RichEditor::make('technical_diagnostic')
                                    ->label('Laudo Técnico (Diagnóstico)')
                                    ->toolbarButtons(['bold', 'italic', 'bulletList', 'orderedList', 'undo', 'redo'])
                                    ->columnSpanFull(),
                                
                                Textarea::make('solution')
                                    ->label('Solução Aplicada / Trabalho Realizado')
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
                                    ->label('Observações de Saída (Ex: Garantia de 3 meses)')
                                    ->rows(3)
                                    ->columnSpanFull(),
                            ]),
                    ]),
            ]);
    }
}