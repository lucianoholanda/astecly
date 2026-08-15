<?php

namespace App\Filament\Resources\Devices;

use App\Filament\Resources\Devices\Pages\CreateDevice;
use App\Filament\Resources\Devices\Pages\EditDevice;
use App\Filament\Resources\Devices\Pages\ListDevices;
use App\Filament\Resources\Devices\Schemas\DeviceForm;
use App\Models\Device;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

// --- COMPONENTES DE AÇÃO (Filament v5) ---
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;

use Illuminate\Database\Eloquent\Model;

class DeviceResource extends Resource
{
    protected static ?string $model = Device::class;

    protected static ?string $navigationLabel = 'Aparelhos';
    protected static ?string $pluralModelLabel = 'Aparelhos';
    protected static ?string $modelLabel = 'Aparelho';
    protected static string|\UnitEnum|null $navigationGroup = 'Cadastros'; 
    protected static string|\BackedEnum|null $navigationIcon = \Filament\Support\Icons\Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'model';

    public static function getRecordTitle(?Model $record): ?string
    {
        if (! $record) {
            return null;
        }

        return "{$record->deviceBrand?->name} {$record->model} SN: {$record->serial_number}";
    }

    public static function form(Schema $schema): Schema
    {
        return DeviceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('type.name')->label('Tipo')->searchable(),
                TextColumn::make('brand.name')->label('Marca')->searchable(),
                TextColumn::make('model')->label('Modelo')->searchable(),
                TextColumn::make('serial_number')->label('Série')->searchable(),
                TextColumn::make('color')->label('Cor'),
            ])
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ServiceOrdersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDevices::route('/'),
            'create' => CreateDevice::route('/create'),
            'edit' => EditDevice::route('/{record}/edit'),
        ];
    }
}