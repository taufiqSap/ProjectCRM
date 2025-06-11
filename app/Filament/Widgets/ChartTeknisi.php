<?php

namespace App\Filament\Widgets;

use Illuminate\Support\Facades\DB;
use Filament\Widgets\ChartWidget;

class ChartTeknisi extends ChartWidget
{
    protected static ?string $heading = 'TOP Teknisi (Frekuensi Penanganan Service)';

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $start = session('dashboard_start_date');
        $end = session('dashboard_end_date');

        $query = DB::table('orders')
            ->join('technicians', 'orders.technician_id', '=', 'technicians.id')
            ->select('technicians.name', DB::raw('COUNT(orders.order_id) as jumlah_service'))
            ->groupBy('technicians.id', 'technicians.name')
            ->orderByDesc('jumlah_service')
            ->limit(5);

        // Apply date filter if exists
        if ($start) $query->whereDate('orders.date', '>=', $start);
        if ($end) $query->whereDate('orders.date', '<=', $end);

        $data = $query->get();

        // If no data, return empty chart
        if ($data->isEmpty()) {
            return [
                'datasets' => [
                    [
                        'label' => 'Jumlah Service',
                        'data' => [],
                        'backgroundColor' => [],
                        'borderColor' => '#ffffff',
                    ],
                ],
                'labels' => [],
            ];
        }

        $labels = $data->pluck('name')->toArray();
        $values = $data->pluck('jumlah_service')->toArray();

        // Generate colors based on data count
        $colors = ['#60A5FA', '#34D399', '#FBBF24', '#A78BFA', '#F87171'];
        $backgroundColor = array_slice($colors, 0, count($values));

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Service',
                    'data' => $values,
                    'backgroundColor' => $backgroundColor,
                    'borderColor' => '#ffffff',
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $labels,
        ];
    }
}