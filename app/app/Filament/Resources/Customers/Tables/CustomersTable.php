<?php

namespace App\Filament\Resources\Customers\Tables;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;

class CustomersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nome')
                    ->searchable(),
                    
                TextColumn::make('document')
                    ->label('CPF / CNPJ')
                    ->searchable()
                    ->formatStateUsing(function (?string $state) {
                        if (!$state) return null;
                        $somenteNumeros = preg_replace('/[^0-9]/', '', $state);
                        if (strlen($somenteNumeros) === 11) {
                            return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $somenteNumeros);
                        }
                        if (strlen($somenteNumeros) === 14) {
                            return preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $somenteNumeros);
                        }
                        return $state;
                    }),

                TextColumn::make('phone')
                    ->label('Telefone')
                    ->searchable()
                    ->formatStateUsing(function (?string $state) {
                        if (!$state) return null;
                        $somenteNumeros = preg_replace('/[^0-9]/', '', $state);
                        if (strlen($somenteNumeros) === 11) {
                            return preg_replace('/(\d{2})(\d{5})(\d{4})/', '($1) $2-$3', $somenteNumeros);
                        }
                        if (strlen($somenteNumeros) === 10) {
                            return preg_replace('/(\d{2})(\d{4})(\d{4})/', '($1) $2-$3', $somenteNumeros);
                        }
                        return $state;
                    }),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),

                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                // NOSSO NOVO BOTÃO DO WHATSAPP
                Action::make('whatsapp')
                    ->label('WhatsApp')
                    ->icon('heroicon-m-chat-bubble-oval-left-ellipsis')
                    ->color('success')
                    ->hiddenLabel()
                    ->tooltip('Abrir no WhatsApp')
                    ->url(function ($record) {
                        $numero = preg_replace('/[^0-9]/', '', (string) $record->phone);
                        return "https://wa.me/55{$numero}";
                    })
                    ->openUrlInNewTab()
                    ->visible(function ($record) {
                        $numero = preg_replace('/[^0-9]/', '', (string) $record->phone);
                        return strlen($numero) === 11;
                    }),

                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}