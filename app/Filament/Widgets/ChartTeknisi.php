<?php

namespace App\Filament\Widgets;

use Illuminate\Support\Facades\DB;
use Filament\Widgets\ChartWidget;

class ChartTeknisi extends ChartWidget
{
    protected static ?string $heading = 'TOP Teknisi (Frekuensi Penanganan Service)';

    protected function getType(): string
    {
        return 'bar'; // Bisa diganti ke 'pie' atau 'doughnut' sesuai preferensi
    }

    protected function getData(): array
    {
        // Hitung jumlah order yang ditangani oleh masing-masing teknisi
        $data = DB::table('orders') // ← ubah dari 'order' ke 'orders'
            ->join('technicians', 'orders.technician_id', '=', 'technicians.id')
            ->select('technicians.name', DB::raw('COUNT(orders.order_id) as jumlah_service'))
            ->groupBy('technicians.id', 'technicians.name')
            ->orderByDesc('jumlah_service')
            ->limit(5)
            ->get();


        $labels = $data->pluck('name')->toArray();
        $values = $data->pluck('jumlah_service')->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Service',
                    'data' => $values,
                    'backgroundColor' => ['#60A5FA', '#34D399', '#FBBF24', '#A78BFA', '#F87171'],
                    'borderColor' => '#ffffff',
                ],
            ],
            'labels' => $labels,
        ];
    }
}
