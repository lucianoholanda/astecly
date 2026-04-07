<?php

namespace App\Filament\Resources\ServiceOrders;

use App\Filament\Resources\ServiceOrders\Pages\CreateServiceOrder;
use App\Filament\Resources\ServiceOrders\Pages\EditServiceOrder;
use App\Filament\Resources\ServiceOrders\Pages\ListServiceOrders;
use App\Filament\Resources\ServiceOrders\Schemas\ServiceOrderForm;
use App\Filament\Resources\ServiceOrders\Tables\ServiceOrdersTable;
use App\Models\ServiceOrder;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ServiceOrderResource extends Resource
{
    protected static ?string $model = ServiceOrder::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'id';

    protected static ?string $modelLabel = 'Ordem de Serviço';
    protected static ?string $pluralModelLabel = 'Ordens de Serviço';

    public static function form(Schema $schema): Schema
    {
        return ServiceOrderForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ServiceOrdersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            // Futuramente as peças entrarão aqui
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListServiceOrders::route('/'),
            'create' => CreateServiceOrder::route('/create'),
            'edit' => EditServiceOrder::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}