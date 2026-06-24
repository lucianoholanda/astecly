<?php

namespace App\Filament\Resources\Customers\Schemas;

use Filament\Support\RawJs;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;
use Filament\Forms\Get;
use Filament\Forms\Set;
use App\Models\Customer;
use Closure;

class CustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Hidden::make('tenant_id')
                    ->default(fn () => auth()->user()->tenant_id ?? 1),

                Hidden::make('customer_type')
                    ->default('PF'),

                Section::make('Informações do Cliente')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nome Completo')
                            ->required()
                            ->columnSpanFull(),

                        TextInput::make('document')
                            ->label('CPF / CNPJ')
                            ->default(null)
                            ->live(onBlur: true)
                            ->rules([
                                'cpf_ou_cnpj',
                                fn (?Model $record) => function (string $attribute, $value, Closure $fail) use ($record) {
                                    $somenteNumeros = preg_replace('/[^0-9]/', '', $value);
                                    $existe = Customer::where('document', $somenteNumeros)
                                        ->when($record, fn ($query) => $query->where('id', '!=', $record->id))
                                        ->exists();
                                    if ($existe) { $fail('Este CPF / CNPJ já está cadastrado.'); }
                                },
                            ])
                            ->mask(RawJs::make(<<<'JS'
                                $input.length > 14 ? '99.999.999/9999-99' : '999.999.999-99'
                            JS))
                            ->dehydrateStateUsing(fn ($state) => $state ? preg_replace('/[^0-9]/', '', $state) : null)
                            ->afterStateUpdated(function (Set $set, ?string $state) {
                                if ($state) {
                                    $somenteNumeros = preg_replace('/[^0-9]/', '', (string) $state);
                                    $set('customer_type', strlen($somenteNumeros) > 11 ? 'PJ' : 'PF');
                                }
                            }),

                        TextInput::make('phone')
                            ->label('Telefone / WhatsApp')
                            ->tel()
                            ->mask(RawJs::make(<<<'JS'
                                $input.length >= 15 ? '(99) 99999-9999' : '(99) 9999-9999'
                            JS))
                            ->dehydrateStateUsing(fn ($state) => $state ? preg_replace('/[^0-9]/', '', $state) : null),

                        TextInput::make('email')
                            ->label('E-mail')
                            ->email()
                            ->columnSpanFull(),
                    ]),

                Section::make('Endereço')
                    ->schema([
                        Repeater::make('addresses')
                            ->relationship('addresses')
                            ->hiddenLabel()
                            ->addActionLabel('Adicionar Endereço')
                            ->defaultItems(1)
                            ->collapsible()
                            ->schema([
                                Hidden::make('tenant_id')->default(fn () => auth()->user()->tenant_id ?? 1),

                                Grid::make(3)->schema([
                                    TextInput::make('zip_code')
                                        ->label('CEP')
                                        ->mask('99999-999')
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(function ($get, $set, ?string $state) {
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
                                        // ->dehydrateStateUsing(fn ($state) => $state ? preg_replace('/[^0-9]/', '', $state) : null),
                                    
                                    TextInput::make('street')->label('Rua')->required()->columnSpan(2),
                                ]),
                                Grid::make(3)->schema([
                                    TextInput::make('number')->label('Número')->required(),
                                    TextInput::make('complement')->label('Complemento'),
                                    TextInput::make('neighborhood')->label('Bairro')->required(),
                                ]),
                                Grid::make(2)->schema([
                                    TextInput::make('city')->label('Cidade')->required(),
                                    TextInput::make('state')->label('Estado')->required(),
                                ]),
                            ])
                            ->columnSpanFull(),
                    ])
            ]);
    }
}