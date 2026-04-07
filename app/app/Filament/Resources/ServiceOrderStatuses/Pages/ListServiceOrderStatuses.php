<?php

namespace App\Filament\Resources\ServiceOrderStatuses\Pages;

use App\Filament\Resources\ServiceOrderStatuses\ServiceOrderStatusResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListServiceOrderStatuses extends ListRecords
{
    protected static string $resource = ServiceOrderStatusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
