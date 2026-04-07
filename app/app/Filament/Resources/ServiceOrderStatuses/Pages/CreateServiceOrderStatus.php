<?php

namespace App\Filament\Resources\ServiceOrderStatuses\Pages;

use App\Filament\Resources\ServiceOrderStatuses\ServiceOrderStatusResource;
use Filament\Resources\Pages\CreateRecord;

class CreateServiceOrderStatus extends CreateRecord
{
    protected static string $resource = ServiceOrderStatusResource::class;
}
