<?php

namespace App\Filament\Resources\CustomerReminderResource\Pages;

use App\Filament\Resources\CustomerReminderResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCustomerReminder extends EditRecord
{
    protected static string $resource = CustomerReminderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
