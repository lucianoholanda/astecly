<?php

namespace App\Filament\Resources\Customers\RelationManagers;

use App\Models\ServiceOrder;
use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ServiceOrdersRelationManager extends RelationManager
{
    protected static string $relationship = 'serviceOrders';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('tenant_id')
                    ->relationship('tenant', 'id')
                    ->required(),
                Select::make('device_id')
                    ->relationship('device', 'id')
                    ->required(),
                TextInput::make('service_order_status_id')
                    ->required()
                    ->numeric(),
                Textarea::make('reported_defect')
                    ->default(null)
                    ->columnSpanFull(),
                Textarea::make('found_defect')
                    ->default(null)
                    ->columnSpanFull(),
                Textarea::make('input_notes')
                    ->default(null)
                    ->columnSpanFull(),
                Textarea::make('diagnosis')
                    ->default(null)
                    ->columnSpanFull(),
                Textarea::make('solution')
                    ->default(null)
                    ->columnSpanFull(),
                Textarea::make('output_notes')
                    ->default(null)
                    ->columnSpanFull(),
                TextInput::make('estimated_cost')
                    ->required()
                    ->numeric()
                    ->default(0.0)
                    ->prefix('$'),
                TextInput::make('final_value')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                DateTimePicker::make('entry_date'),
                DateTimePicker::make('exit_date'),
                Textarea::make('photos')
                    ->default(null)
                    ->columnSpanFull(),
            ]);
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('tenant.id')
                    ->label('Tenant'),
                TextEntry::make('device.id')
                    ->label('Device'),
                TextEntry::make('service_order_status_id')
                    ->numeric(),
                TextEntry::make('reported_defect')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('found_defect')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('input_notes')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('diagnosis')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('solution')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('output_notes')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('estimated_cost')
                    ->money(),
                TextEntry::make('final_value')
                    ->numeric(),
                TextEntry::make('entry_date')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('exit_date')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('photos')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn (ServiceOrder $record): bool => $record->trashed()),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('tenant.id')
                    ->searchable(),
                TextColumn::make('device.id')
                    ->searchable(),
                TextColumn::make('service_order_status_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('estimated_cost')
                    ->money()
                    ->sortable(),
                TextColumn::make('final_value')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('entry_date')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('exit_date')
                    ->dateTime()
                    ->sortable(),
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
            ->headerActions([
                CreateAction::make(),
                AssociateAction::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DissociateAction::make(),
                DeleteAction::make(),
                ForceDeleteAction::make(),
                RestoreAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DissociateBulkAction::make(),
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->modifyQueryUsing(fn (Builder $query) => $query
                ->withoutGlobalScopes([
                    SoftDeletingScope::class,
                ]));
    }
}
