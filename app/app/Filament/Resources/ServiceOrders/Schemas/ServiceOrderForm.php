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
                        // ABA 1: RECEPÇÃO
                        // ==========================================
                        Tab::make('1. Recepção do Aparelho')
                            ->icon('heroicon-m-inbox-arrow-down')
                            ->schema([
                                
                                // SECTION 1: IDENTIFICAÇÃO E ATENDIMENTO
                                Section::make('1. Identificação e Atendimento')
                                    ->columns(2)
                                    ->schema([
                                        Select::make('customer_id')
                                            ->label('Pesquisar Cliente')
                                            ->relationship('customer', 'name')
                                            ->searchable(['name', 'document', 'phone'])
                                            ->placeholder('Deixe em branco para cadastrar um novo cliente...')
                                            ->preload(false)
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
                                                $set('customer_address_id', null);
                                            })
                                            ->columnSpanFull(),

                                        TextInput::make('customer_name')->label('Nome Completo')->required(),
                                        TextInput::make('customer_document')
                                            ->label('CPF / CNPJ')
                                            ->disabled(fn ($get) => filled($get('customer_id')))
                                            ->mask(RawJs::make(<<<'JS'
                                                $input.length > 14 ? '99.999.999/9999-99' : '999.999.999-99'
                                            JS)),
                                        TextInput::make('customer_phone')->label('Telefone / WhatsApp')->tel()
                                            ->mask(RawJs::make(<<<'JS'
                                                $input.length >= 15 ? '(99) 99999-9999' : '(99) 9999-9999'
                                            JS)),
                                        TextInput::make('customer_email')->label('E-mail')->email(),
                                        
                                        // MODALIDADE E MARKETING LADO A LADO
                                        Select::make('marketing_source_id')
                                            ->label('Como conheceu a loja?')
                                            ->options(\App\Models\MarketingSource::pluck('name', 'id'))
                                            ->searchable()
                                            ->visible(fn ($get) => empty($get('customer_id'))),
                                            
                                        Select::make('service_modality_id')
                                            ->label('Modalidade')
                                            ->relationship('modality', 'name')
                                            ->required()
                                            ->live(),
                                            
                                        DateTimePicker::make('scheduled_at')
                                            ->label('Data do Agendamento')
                                            ->seconds(false)
                                            ->displayFormat('d/m/Y H:i')
                                            ->visible(fn ($get) => ServiceModality::find($get('service_modality_id'))?->requires_scheduling ?? false)
                                            ->required(fn ($get) => ServiceModality::find($get('service_modality_id'))?->requires_scheduling ?? false)
                                            ->columnSpanFull(),
                                    ]),

                                // SECTION 2: ENDEREÇO DO CLIENTE (Sempre disponível)
                                Section::make('2. Endereço do Cliente')
                                    ->schema([
                                        Select::make('customer_address_id')
                                            ->label('Endereços Cadastrados')
                                            ->placeholder('Preencha os campos abaixo para salvar um novo endereço...')
                                            ->options(fn ($get) => CustomerAddress::where('customer_id', $get('customer_id'))->get()->mapWithKeys(fn ($a) => [$a->id => "{$a->street}, {$a->number}"]))
                                            ->live()
                                            ->visible(fn ($get) => filled($get('customer_id'))),

                                        Grid::make(1)
                                            ->schema([
                                                Grid::make(3)->schema([
                                                    TextInput::make('zip_code')->label('CEP')->mask('99999-999')->live(onBlur: true)
                                                        ->afterStateUpdated(function ($set, ?string $state) {
                                                            $cep = preg_replace('/[^0-9]/', '', (string)$state);
                                                            if (strlen($cep) !== 8) return;
                                                            $response = Http::get("https://viacep.com.br/ws/{$cep}/json/");
                                                            if ($response->successful() && !$response->json('erro')) {
                                                                $data = $response->json();
                                                                $set('street', $data['logradouro'] ?? null); $set('neighborhood', $data['bairro'] ?? null);
                                                                $set('city', $data['localidade'] ?? null); $set('state', $data['uf'] ?? null);
                                                            }
                                                        }),
                                                    TextInput::make('street')->label('Rua/Avenida')->columnSpan(2),
                                                ]),
                                                Grid::make(3)->schema([
                                                    TextInput::make('number')->label('Número'),
                                                    TextInput::make('complement')->label('Complemento'),
                                                    TextInput::make('neighborhood')->label('Bairro'),
                                                ]),
                                                Grid::make(2)->schema([
                                                    TextInput::make('city')->label('Cidade'),
                                                    TextInput::make('state')->label('UF')->length(2),
                                                ]),
                                            ])
                                            ->visible(fn ($get) => empty($get('customer_address_id'))) // Aparece sempre que não houver um endereço pronto selecionado
                                    ]),

                                // SECTION 2: DADOS DO EQUIPAMENTO
                                Section::make('2. Dados do Equipamento')
                                    ->columns(3)
                                    ->schema([
                                        Select::make('device_id')
                                            ->label('Aparelho Registrado')
                                            ->options(fn ($get) => Device::where('customer_id', $get('customer_id'))->pluck('model', 'id'))
                                            ->placeholder('Cadastrar novo aparelho...')
                                            ->live()
                                            ->afterStateUpdated(function ($set, ?string $state) {
                                                if ($state && $device = Device::find($state)) {
                                                    $set('device_type_id', $device->device_type_id); $set('device_brand_id', $device->device_brand_id);
                                                    $set('device_model', $device->model); $set('device_serial_number', $device->serial_number);
                                                    $set('color', $device->color); 
                                                }
                                            })
                                            ->columnSpanFull()
                                            ->visible(fn ($get) => filled($get('customer_id'))),

                                        Select::make('device_type_id')->label('Tipo')->options(DeviceType::pluck('name', 'id'))->searchable(),
                                        Select::make('device_brand_id')->label('Marca')->options(DeviceBrand::pluck('name', 'id'))->searchable(),
                                        TextInput::make('device_model')->label('Modelo')->required(),
                                        TextInput::make('device_serial_number')->label('Número de Série'),
                                        TextInput::make('color')->label('Cor do Aparelho'),

                                        TextInput::make('accessories')
                                            ->label('Acessórios Deixados (Cabos, Controle, etc)')
                                            ->columnSpanFull(),
                                            
                                        Textarea::make('equipment_condition')
                                            ->label('Estado Físico do Aparelho')
                                            ->placeholder('Ex: Riscos na tela, canto amassado...')
                                            ->rows(2)
                                            ->columnSpanFull(),
                                    ]),

                                // SECTION 3: TRIAGEM E LAUDO DE ENTRADA
                                Section::make('3. Triagem e Relato')
                                    ->columns(1)
                                    ->schema([
                                        Textarea::make('customer_report')
                                            ->label('Relato do Cliente')
                                            ->placeholder('O que o cliente diz que aconteceu...')
                                            ->nullable()
                                            ->rows(3),

                                        Textarea::make('initial_symptom')
                                            ->label('Defeito Constatado (Teste de Balcão)')
                                            ->placeholder('O que o técnico constatou na hora...')
                                            ->nullable()
                                            ->rows(3),

                                        Textarea::make('general_observations')
                                            ->label('Observações Adicionais')
                                            ->rows(2),
                                    ]),
                            ]), 

                        // ==========================================
                        // ABA 2: LABORATÓRIO
                        // ==========================================
                        Tab::make('2. Laboratório e Diagnóstico')
                            ->icon('heroicon-m-wrench-screwdriver')
                            ->hidden(fn (string $operation): bool => $operation === 'create')
                            ->schema([
                                RichEditor::make('technical_diagnostic')->label('Laudo Técnico (Diagnóstico)')->columnSpanFull(),
                                Textarea::make('solution')->label('Solução Aplicada')->rows(4)->columnSpanFull(),
                            ]),

                        // ==========================================
                        // ABA 3: COMERCIAL / FINANCEIRO
                        // ==========================================
                        Tab::make('3. Orçamento e Encerramento')
                            ->icon('heroicon-m-currency-dollar')
                            ->hidden(fn (string $operation): bool => $operation === 'create')
                            ->columns(2)
                            ->schema([
                                TextInput::make('estimated_cost')->label('Custo Estimado (Orçamento)')->numeric()->prefix('R$'),
                                TextInput::make('final_value')->label('Valor Final (Cobrado)')->numeric()->prefix('R$'),
                                DatePicker::make('exit_date')->label('Data de Saída / Entrega')->displayFormat('d/m/Y'),
                                Textarea::make('output_notes')->label('Observações de Saída')->rows(3)->columnSpanFull(),
                            ]),
                    ]),
            ]);
    }
}