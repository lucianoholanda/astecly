<?php

namespace App\Filament\Resources\ServiceOrderStatuses\Pages;

use App\Filament\Resources\ServiceOrderStatuses\ServiceOrderStatusResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditServiceOrderStatus extends EditRecord
{
    protected static string $resource = ServiceOrderStatusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
