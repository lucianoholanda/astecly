<?php

namespace App\Filament\Resources\ServiceOrderStatuses;

use App\Filament\Resources\ServiceOrderStatuses\Pages\CreateServiceOrderStatus;
use App\Filament\Resources\ServiceOrderStatuses\Pages\EditServiceOrderStatus;
use App\Filament\Resources\ServiceOrderStatuses\Pages\ListServiceOrderStatuses;
use App\Filament\Resources\ServiceOrderStatuses\Schemas\ServiceOrderStatusForm;
use App\Filament\Resources\ServiceOrderStatuses\Tables\ServiceOrderStatusesTable;
use App\Models\ServiceOrderStatus;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ServiceOrderStatusResource extends Resource
{
    protected static ?string $model = ServiceOrderStatus::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return ServiceOrderStatusForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ServiceOrderStatusesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListServiceOrderStatuses::route('/'),
            'create' => CreateServiceOrderStatus::route('/create'),
            'edit' => EditServiceOrderStatus::route('/{record}/edit'),
        ];
    }
}
