<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class ChartATahunan extends ChartWidget
{
    protected static ?string $heading = 'Penghasilan Per Tahun';

    protected function getData(): array
    {
        $start = session('dashboard_start_date');
        $end = session('dashboard_end_date');

        $query = DB::table('orders as o')
            ->join('order_details as od', 'o.order_detail_id', '=', 'od.id')
            ->join('order_parts as op', 'o.order_part_id', '=', 'op.id')
            ->selectRaw('YEAR(o.date) as tahun, SUM(COALESCE(od.total_amount, 0) + (COALESCE(op.qty, 0) * COALESCE(op.part_price, 0))) as total')
            ->groupBy('tahun')
            ->orderBy('tahun');

        // Apply date filter if exists
        if ($start) $query->whereDate('o.date', '>=', $start);  
        if ($end) $query->whereDate('o.date', '<=', $end);

        $data = $query->pluck('total', 'tahun')->toArray();

        // If no data, return empty chart
        if (empty($data)) {
            return [
                'datasets' => [
                    [
                        'label' => 'Penghasilan',
                        'data' => [],
                        'borderColor' => '#10b981',
                        'backgroundColor' => 'rgba(16, 185, 129, 0.2)',
                    ],
                ],
                'labels' => [],
            ];
        }

        $labels = array_keys($data);
        $values = array_values($data);

        return [
            'datasets' => [
                [
                    'label' => 'Penghasilan',
                    'data' => $values,
                    'borderColor' => '#10b981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.2)',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar'; 
    }
}