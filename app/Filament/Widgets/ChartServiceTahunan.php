<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class ChartServiceTahunan extends ChartWidget
{
    protected static ?string $heading = 'Jumlah Service per Tahun';

    protected function getData(): array
    {
        $start = session('dashboard_start_date');
        $end = session('dashboard_end_date');

        $query = DB::table('orders')
            ->selectRaw('YEAR(date) as year, COUNT(*) as total');

        if ($start) $query->whereDate('date', '>=', $start);
        if ($end) $query->whereDate('date', '<=', $end);

        $data = $query->groupByRaw('YEAR(date)')
            ->orderByRaw('YEAR(date)')
            ->pluck('total', 'year');

        return [
            'datasets' => [
                [
                    'label' => 'Service per Tahun',
                    'data' => $data->values(),
                ]
            ],
            'labels' => $data->keys(),
        ];
    }

    protected function getType(): string
    {
        return 'bar'; // Bisa juga 'line' atau 'pie' sesuai preferensi
    }
}
