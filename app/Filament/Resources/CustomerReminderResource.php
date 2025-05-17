<?php

namespace App\Filament\Resources;

use App\Models\Customer;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;

class CustomerReminderResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';
    protected static ?string $navigationLabel = 'Reminder';
    protected static ?string $navigationGroup = 'CRM';

    public static function getEloquentQuery(): Builder
    {
        $thresholdDate = now()->subMonths(3); // batas 3 bulan tidak kembali

        return Customer::query()
            ->whereIn('id', function ($query) use ($thresholdDate) {
                $query->select('customer_id')
                    ->from('orders')
                    ->groupBy('customer_id')
                    ->havingRaw('MAX(date) < ?', [$thresholdDate]);
            });
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Nama Pelanggan')->searchable(),
                TextColumn::make('no_telp')->label('No. Telepon'),
                TextColumn::make('date')
                    ->label('Layanan Terakhir')
                    ->getStateUsing(fn ($record) => $record->orders()->max('date'))
                    ->date()
                    ->sortable()
                    ->default('-'),
            ])
            ->actions([]) 
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\CustomerReminderResource\Pages\ListCustomerReminders::route('/'),
        ];
    }
}
