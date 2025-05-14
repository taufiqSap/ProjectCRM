<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderPartResource\Pages;
use App\Filament\Resources\OrderPartResource\RelationManagers;
use App\Models\OrderPart;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrderPartResource extends Resource
{
    protected static ?string $model = OrderPart::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('part_id')->relationship('part', 'part_name')->required(),
                TextInput::make('part_price')->numeric()->required(),
                TextInput::make('qty')->numeric()->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrderParts::route('/'),
            'create' => Pages\CreateOrderPart::route('/create'),
            'edit' => Pages\EditOrderPart::route('/{record}/edit'),
        ];
    }
}
