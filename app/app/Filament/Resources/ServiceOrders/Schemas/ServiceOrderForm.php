<?php

namespace App\Filament\Resources\ServiceOrders\Schemas;

// --- COMPONENTES DE ESTRUTURA (Schemas) ---
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs;         // NOVO: Adicionado Tabs
use Filament\Schemas\Components\Tabs\Tab;     // NOVO: Adicionado Tab
use Filament\Schemas\Schema;

// --- COMPONENTES DE PREENCHIMENTO (Forms) ---
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DatePicker;     // NOVO: Para datas de saída/previsão
use Filament\Forms\Components\RichEditor;     // NOVO: Para o laudo técnico ficar bonito

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
                // O componente Tabs agrupa tudo em abas navegáveis
                Tabs::make('Painel da OS')
                    ->tabs([
                        // ==========================================
                        // ABA 1: RECEPÇÃO (Sempre visível)
                        // ==========================================
                        Tab::make('1. Recepção do Aparelho')
                            ->icon('heroicon-m-inbox-arrow-down') // Um ícone bacana
                            ->schema([

                            Section::make('1. Identificação do Cliente')
                                ->description(fn (string $operation) => $operation === 'create' ? 'Busque pelo nome, CPF/CNPJ ou telefone, ou preencha para cadastrar um novo.' : 'Dados do cliente vinculados a esta Ordem de Serviço.')
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
                                        // MÁGICA 1: Oculta a barra de pesquisa na Edição
                                        ->hidden(fn (string $operation) => $operation === 'edit'),

                                    TextInput::make('customer_name')
                                        ->label('Nome Completo')
                                        ->required(fn (string $operation) => $operation === 'create')
                                        // MÁGICA 2: Desabilita e puxa a info do banco na Edição
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

                            Section::make('2. Dados do Equipamento')
                                ->columns(2) // Ajustado para 2 colunas para ficar simétrico com 4 campos
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
                                            }
                                        })
                                        ->columnSpanFull()
                                        ->hidden(fn (string $operation, $get) => $operation === 'edit' || ! $get('customer_id')),

                                    Select::make('device_type_id')
                                        ->label('Tipo')
                                        ->options(DeviceType::pluck('name', 'id'))
                                        ->required(fn (string $operation) => $operation === 'create')
                                        ->preload()
                                        ->searchable()
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
                                        // Continua editável na edição
                                        ->formatStateUsing(fn ($record, $state) => $record ? $record->device?->model : $state),
                                    
                                    TextInput::make('device_serial_number')
                                        ->label('Número de Série / IMEI')
                                        // Novo campo, continua editável na edição
                                        ->formatStateUsing(fn ($record, $state) => $record ? $record->device?->serial_number : $state),
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

                            ]), // Fim da Aba 1

                        // ==========================================
                        // ABA 2: LABORATÓRIO (Oculta na criação)
                        // ==========================================
                        Tab::make('2. Laboratório e Diagnóstico')
                            ->icon('heroicon-m-wrench-screwdriver')
                            // A MÁGICA: Só mostra essa aba se a operação NÃO for 'create'
                            ->hidden(fn (string $operation): bool => $operation === 'create')
                            ->schema([
                                RichEditor::make('technical_diagnostic') // Editor de texto rico para o laudo
                                    ->label('Laudo Técnico (Diagnóstico)')
                                    ->toolbarButtons([
                                        'bold', 'italic', 'bulletList', 'orderedList', 'undo', 'redo',
                                    ])
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
                                // Nota: As peças entrarão nesta tela mais pra frente usando RelationManagers!
                                
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