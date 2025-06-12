<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ChartHarian extends ChartWidget
{
    protected static ?string $heading = 'Penghasilan Harian';

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $start = session('dashboard_start_date');
        $end = session('dashboard_end_date');

        $query = DB::table('orders as o')
            ->join('order_details as od', 'o.order_detail_id', '=', 'od.id')
            ->join('order_parts as op', 'o.order_part_id', '=', 'op.id')
            ->selectRaw('DATE(o.date) as tanggal, SUM(COALESCE(od.total_amount, 0) + (COALESCE(op.qty, 0) * COALESCE(op.part_price, 0))) as total')
            ->groupByRaw('DATE(o.date)')
            ->orderBy('tanggal');

        // Apply date filter if exists
        if ($start) $query->whereDate('o.date', '>=', $start);
        if ($end) $query->whereDate('o.date', '<=', $end);

        $data = $query->pluck('total', 'tanggal');

        // If no data, return empty chart
        if ($data->isEmpty()) {
            return [
                'datasets' => [
                    [
                        'label' => 'Penghasilan',
                        'data' => [],
                        'fill' => false,
                        'borderColor' => '#4f46e5',
                        'backgroundColor' => 'rgba(79, 70, 229, 0.1)',
                    ],
                ],
                'labels' => [],
            ];
        }

        $labels = $data->keys()->map(fn ($d) => Carbon::parse($d)->format('d M'))->toArray();
        $values = $data->values()->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Penghasilan',
                    'data' => $values,
                    'fill' => false,
                    'borderColor' => '#4f46e5',
                    'backgroundColor' => 'rgba(79, 70, 229, 0.1)',
                    'tension' => 0.4,
                ],
            ],
            'labels' => $labels,
        ];
    }
}