<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Contracts\HasForms;
use Illuminate\Support\Facades\DB;
use App\Models\Customer;
use App\Models\Vehicle;
use App\Models\Technician;
use App\Models\ServiceCategory;
use App\Models\OrderDetail;
use App\Models\Order;
use App\Models\Part;
use App\Models\OrderPart;

class ExcelImportPage extends Page implements HasForms
{
    use Forms\Concerns\InteractsWithForms;

    public $file;

    protected static string $view = 'filament.pages.excel-import-page';
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Data Management';
    protected static ?string $slug = 'excel-import';



    public function mount(): void
    {
        $this->form->fill();
    }

    protected function getFormSchema(): array
    {
        return [
            FileUpload::make('file')
                ->label('Import Excel File')
                ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])
                ->directory('excel-uploads')
                ->required(),
        ];
    }

    public function submit(): void
    {
        $data = $this->form->getState();
        $path = storage_path('app/public/' . $data['file']);

        $processor = app(\App\Services\ExcelProcessorService::class);
        $rows = $processor->process($path);

        foreach ($rows as $row) {
            DB::transaction(function () use ($row) {
                // 1. Customer
                $customer = Customer::firstOrCreate(
                    ['no_telp' => $row['no_telp'], 'name' => $row['nama']],
                    ['created_at' => now(), 'updated_at' => now()]
                );

                // 2. Vehicle
                $vehicle = Vehicle::firstOrCreate(
                    ['no_frame' => $row['no_frame']],
                    [
                        'brand'      => $row['brand'],
                        'model_name' => $row['model_name'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );

                // 3. Technician
                $tech = Technician::firstOrCreate(
                    ['name' => $row['technician_name']],
                    ['created_at' => now(), 'updated_at' => now()]
                );

                // 4. Service Category/Package
                $svcCat = ServiceCategory::firstOrCreate(
                    ['category' => $row['service_category'], 'package' => $row['service_package']],
                    ['created_at' => now(), 'updated_at' => now()]
                );

                

        
                // 7. Part
                $part = Part::firstOrCreate(
                    ['no_part' => $row['no_part']],
                    [
                        'part_name'  => $row['part_name'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );

                // 8. OrderPart
                $orderPart = OrderPart::create([
                    'part_id'     => $part->id,
                    'part_price'  => $row['part_price'],
                    'qty'         => $row['part_qty'],
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);

                 
                if (!isset($row['no_invoice']) || trim($row['no_invoice']) === '' || 
                    !isset($row['labor_cost_service']) || trim($row['labor_cost_service']) === '') {
                    throw new \Exception("Data order detail tidak lengkap di baris ke-{$row['row_id']}");
                }

                // 1. Buat OrderDetail dulu
                $detail = OrderDetail::create([
                    'no_invoice' => $row['no_invoice'],
                    'labor_cost_service' => (float)$row['labor_cost_service'], // pastikan tipe data float
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Pastikan OrderDetail berhasil dibuat
                if (!$detail->id) {
                    throw new \Exception("Gagal membuat order detail");
    }

                // Ubah bagian pembuatan Order menjadi:
                $order = Order::create([
                    'service_order'      => $row['service_order'],
                    'date'               => $row['date'],
                    'order_detail_id'    => $detail->id,
                    'service_package_id' => $svcCat->id,
                    'customer_id'        => $customer->id,
                    'vehicle_id'         => $vehicle->id,
                    'technician_id'      => $tech->id,
                    'total_oil_service'  => $row['total_oil_service'],
                    'order_part_id'      => $orderPart->id, // Langsung isi dengan ID yang sudah dibuat
                    'created_at'         => now(),
                    'updated_at'         => now(),
                ]);
               
            });
        }

        \Filament\Notifications\Notification::make()
            ->success()
            ->title('Data imported successfully!')
            ->send();
    }
}
