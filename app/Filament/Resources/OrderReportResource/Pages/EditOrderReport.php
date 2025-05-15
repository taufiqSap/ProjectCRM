<?php

namespace App\Filament\Resources\OrderReportResource\Pages;

use App\Filament\Resources\OrderReportResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOrderReport extends EditRecord
{
    protected static string $resource = OrderReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
