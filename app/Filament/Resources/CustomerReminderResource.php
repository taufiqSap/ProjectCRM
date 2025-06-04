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

        return DB::table('service_categories')
            ->where('id', $lastOrder->service_package_id)
            ->value('package') ?? '-';
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

       Tables\Actions\Action::make('kirimReminderPerKategoriManual')
    ->label('Kirim Reminder per Kategori (Manual)')
    ->icon('heroicon-o-paper-airplane')
    ->form([
        \Filament\Forms\Components\Select::make('kategori_id')
            ->label('Pilih Kategori Servis')
            ->options(DB::table('service_categories')->pluck('package', 'id')->toArray())
            ->required(),

        \Filament\Forms\Components\Select::make('customer_ids')
            ->label('Pilih Pelanggan')
            ->options(
                Customer::all()->pluck('name', 'id')->toArray()
            )
            ->multiple()
            ->searchable()
            ->required(),
    ])
    ->action(function (array $data) {
        $kategoriId = $data['kategori_id'];
        $kategoriName = DB::table('service_categories')->where('id', $kategoriId)->value('package');
        $customerIds = $data['customer_ids'];

        $success = 0;
        $failed = 0;

        foreach ($customerIds as $customerId) {
            $customer = Customer::find($customerId);

            $lastOrder = DB::table('orders')
                ->where('customer_id', $customerId)
                ->orderByDesc('date')
                ->first();

            if (!$customer || !$customer->no_telp || !$lastOrder || $lastOrder->service_package_id != $kategoriId) {
                $failed++;
                continue;
            }

            $pesan = "Halo, Anda belum servis untuk kategori: {$kategoriName} tolong segera di service ya, agar motor tetap seperti baru!.";

            try {
                \App\Services\WhatsappService::send($customer->no_telp, $pesan);
                $success++;
            } catch (\Exception $e) {
                $failed++;
            }
        }

        \Filament\Notifications\Notification::make()
            ->title('Reminder per Kategori Manual')
            ->body("Kategori: {$kategoriName} — Berhasil: {$success}, Gagal: {$failed}")
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
