<?php

namespace App\Filament\Resources;

use App\Models\Order;
use App\Models\Customer;
use App\Models\Vehicle;
use App\Models\Technician;
use App\Models\ServiceCategory;
use App\Models\OrderDetail;
use App\Models\OrderPart;
use Filament\Resources\Resource;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Filament\Resources\OrderResource\Pages\ListOrders;
use App\Filament\Resources\OrderResource\Pages\CreateOrder;
use App\Filament\Resources\OrderResource\Pages\EditOrder;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('service_order')->required(),
            Forms\Components\DatePicker::make('date')->required(),

            Forms\Components\Select::make('customer_id')
                ->relationship('customer', 'name')
                ->required(),

            Forms\Components\Select::make('vehicle_id')
                ->relationship('vehicle', 'model_name')
                ->required(),

            Forms\Components\Select::make('technician_id')
                ->relationship('technician', 'name'),

            Forms\Components\Select::make('order_detail_id')
                ->relationship('orderDetail', 'no_invoice'),

            Forms\Components\Select::make('service_package')
                ->relationship('serviceCategory', 'package'),

            Forms\Components\Select::make('order_part_id')
                ->relationship('orderPart', 'id'),

            Forms\Components\TextInput::make('total_oil_service')
                ->numeric(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('service_order')->searchable(),
            Tables\Columns\TextColumn::make('customer.name')->label('Customer'),
            Tables\Columns\TextColumn::make('vehicle.model_name')->label('Vehicle'),
            Tables\Columns\TextColumn::make('technician.name')->label('Technician'),
            Tables\Columns\TextColumn::make('date'),
        ])
        ->filters([])
        ->actions([
            Tables\Actions\EditAction::make(),
        ])
        ->bulkActions([
            Tables\Actions\DeleteBulkAction::make(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOrders::route('/'),
            'create' => CreateOrder::route('/create'),
            'edit' => EditOrder::route('/{record}/edit'),
        ];
    }
}

