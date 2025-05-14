<?php

namespace App\Filament\Resources\OrderPartResource\Pages;

use App\Filament\Resources\OrderPartResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOrderParts extends ListRecords
{
    protected static string $resource = OrderPartResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
