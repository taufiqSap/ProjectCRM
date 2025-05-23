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
                    ->sortable()
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
            ->actions([
                Tables\Actions\Action::make('kirimReminder')
        ->label('Kirim Notifikasi')
        ->icon('heroicon-m-paper-airplane')
        ->form([
            \Filament\Forms\Components\Select::make('jenis_servis')
                ->label('Jenis Servis')
                ->options([
                    'ganti_oli' => 'Ganti Oli',
                    'service_ringan' => 'Service Ringan',
                    'service_berat' => 'Service Berat',
                ])
                ->required(),
        ])
      ->action(function (Customer $record) {
    $lastOrder = DB::table('orders')
        ->where('customer_id', $record->id)
        ->orderByDesc('date')
        ->first();

    if (!$lastOrder) {
        \Filament\Notifications\Notification::make()
            ->danger()
            ->title('Pelanggan ini belum pernah servis.')
            ->send();
        return;
    }

    $lastServiceDate = \Carbon\Carbon::parse($lastOrder->date);
    $diffInMonths = $lastServiceDate->diffInMonths(now());

    if ($diffInMonths < 3) {
        \Filament\Notifications\Notification::make()
            ->info()
            ->title("Servis terakhir baru $diffInMonths bulan lalu. Belum waktunya kirim notifikasi.")
            ->send();
        return;
    }

    // Ambil kategori servis dari service_package_id
    $serviceCategory = DB::table('service_categories')
        ->where('id', $lastOrder->service_package_id)
        ->value('package');

    $pesan = match (strtolower($serviceCategory)) {
        'ganti oli' => "Halo {$record->name}, sudah lebih dari 3 bulan sejak ganti oli terakhir. Jangan lupa ganti oli ya!",
        'service ringan' => "Halo {$record->name}, sudah lebih dari 3 bulan sejak servis ringan terakhir. Waktunya service ringan lagi!",
        'service berat' => "Halo {$record->name}, sudah lebih dari 3 bulan sejak servis berat terakhir. Kami siap bantu servis berat Anda!",
        default => "Halo {$record->name}, sudah lebih dari 3 bulan sejak servis terakhir. Jangan lupa servis kendaraan Anda!",
    };

    if (!$record->no_telp) {
        \Filament\Notifications\Notification::make()
            ->danger()
            ->title('Nomor telepon tidak tersedia.')
            ->send();
        return;
    }

    \App\Services\WhatsappService::send($record->no_telp, $pesan);

    \Filament\Notifications\Notification::make()
        ->success()
        ->title('Reminder berhasil dikirim ke WhatsApp.')
        ->send();
})


        ])
            ->bulkActions([
                   Tables\Actions\DeleteBulkAction::make(),
            ]);
    }


    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\CustomerReminderResource\Pages\ListCustomerReminders::route('/'),
        ];
    }
}
