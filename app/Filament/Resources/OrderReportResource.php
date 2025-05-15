<?php

namespace App\Filament\Resources;

use App\Models\Order;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;

class OrderReportResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-ellipsis';
    protected static ?string $navigationLabel = 'Order Reports';
    protected static ?string $navigationGroup = 'Reports';

    public static function getEloquentQuery(): Builder
    {
        return Order::query()
            ->join('customer', 'order.customer_id', '=', 'customer.id')
            ->select('order.*', 'customer.nama as customer_name');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('Order ID')->sortable(),
                TextColumn::make('customer_name')->label('Customer')->searchable(),
                TextColumn::make('total')->label('Total')->money('IDR'),
                TextColumn::make('created_at')->label('Order Date')->dateTime(),
            ])
            ->filters([
                //
            ])
            ->actions([
                // Tidak ada edit/delete karena ini laporan
            ])
            ->bulkActions([
                // Tidak perlu bulk action
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\OrderReportResource\Pages\ListOrderReports::route('/'),
        ];
    }
}
