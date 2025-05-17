<?php

namespace App\Filament\Resources\CustomerReminderResource\Pages;

use App\Filament\Resources\CustomerReminderResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCustomerReminder extends CreateRecord
{
    protected static string $resource = CustomerReminderResource::class;
}
