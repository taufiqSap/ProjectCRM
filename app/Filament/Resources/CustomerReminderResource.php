<?php

namespace App\Filament\Resources;

use App\Models\Customer;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Filters\SelectFilter;

class CustomerReminderResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';
    protected static ?string $navigationLabel = 'Reminder';
    protected static ?string $navigationGroup = 'CRM';

    public static function getEloquentQuery(): Builder
{
    $thresholdDate = now()->subMonths(3); // batas 3 bulan tidak servis

    return Customer::query()
        ->whereIn('id', function ($query) use ($thresholdDate) {
            $query->select('customer_id')
                ->from('orders')
                ->whereIn('service_package_id', function ($sub) {
                    $sub->select('id')
                        ->from('service_categories')
                        ->whereIn('package', ['Ganti Oli Gear','Ganti Oli Mesin', 'Service Ringan']);
                })
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
               TextColumn::make('kategori_servis')
    ->label('Kategori Servis')
    ->formatStateUsing(function ($record) {
        $lastOrder = DB::table('orders')
            ->where('customer_id', $record->id)
            ->orderByDesc('date')
            ->first();

        if (!$lastOrder || !$lastOrder->service_package_id) {
            return '-';
        }

        $category = DB::table('service_categories')
            ->where('id', $lastOrder->service_package_id)
            ->value('package');

        if (!in_array($category, ['Ganti Oli Gear','Ganti Oli Mesin', 'Service Ringan'] )){
            return '-';
        }

        return $category;
    })
    ->default('-'),
                TextColumn::make('last_service_date')
                    ->label('Layanan Terakhir')
                    
                    ->default('-'),
            ])
           ->filters([
                SelectFilter::make('kategori_servis')
                    ->label('Kategori Servis')
                    ->options(function () {
                        return DB::table('service_categories')
                            ->orderBy('package')
                            ->pluck('package', 'id')
                            ->toArray();
                    })
                    ->query(function (Builder $query, array $data) {
                        if (!isset($data['value'])) return;

                        $query->whereIn('id', function ($subQuery) use ($data) {
                            $subQuery->select('customer_id')
                                ->from('orders')
                                ->where('service_package_id', $data['value']);
                        });
                    }),
            ])
            ->bulkActions([
                   Tables\Actions\DeleteBulkAction::make(),
            ])
            ->headerActions([
    Tables\Actions\Action::make('kirimSemuaReminder')
        ->label('Kirim Semua Reminder')
        ->icon('heroicon-m-bell-alert')
        ->requiresConfirmation()
        ->action(function () {
            $thresholdDate = now()->subMonths(3);

            $customers = Customer::whereIn('id', function ($query) use ($thresholdDate) {
                $query->select('customer_id')
                    ->from('orders')
                    ->whereIn('service_package_id', function ($sub) {
                        $sub->select('id')
                            ->from('service_categories')
                            ->whereIn('package', ['Ganti Oli Gear','Ganti Oli Mesin', 'Service Ringan']);
                    })
                    ->groupBy('customer_id')
                    ->havingRaw('MAX(date) < ?', [$thresholdDate]);
            })->get();

            $success = 0;
            $failed = 0;

            foreach ($customers as $customer) {
                $lastOrder = DB::table('orders')
                    ->where('customer_id', $customer->id)
                    ->orderByDesc('date')
                    ->first();

                if (!$lastOrder || !$customer->no_telp) {
                    $failed++;
                    continue;
                }

                $serviceCategory = DB::table('service_categories')
                    ->where('id', $lastOrder->service_package_id)
                    ->value('package');

                if (!in_array($serviceCategory, ['Ganti Oli Gear','Ganti Oli Mesin', 'Service Ringan'])) {
                    $failed++;
                    continue;
                }

                $pesan = "Halo, sudah lebih dari 3 bulan Anda servis dan ganti oli, tolong segera servis ya.";

                try {
                    \App\Services\WhatsappService::send($customer->no_telp, $pesan);
                    $success++;
                } catch (\Exception $e) {
                    $failed++;
                }
            }

            \Filament\Notifications\Notification::make()
                ->title('Reminder Massal Selesai')
                ->body("Berhasil dikirim: {$success}, Gagal: {$failed}")
                ->success()
                ->send();
        }),
    ]);


    }


    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\CustomerReminderResource\Pages\ListCustomerReminders::route('/'),
        ];
    }
}
