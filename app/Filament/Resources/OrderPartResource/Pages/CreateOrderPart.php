<?php

namespace App\Filament\Resources\OrderPartResource\Pages;

use App\Filament\Resources\OrderPartResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateOrderPart extends CreateRecord
{
    protected static string $resource = OrderPartResource::class;
}
