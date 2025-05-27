<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class ChartATahunan extends ChartWidget
{
    protected static ?string $heading = 'Penghasilan Per Tahun';

    protected function getData(): array
    {
        $data = DB::table('orders as o')
            ->join('order_details as od', 'o.order_detail_id', '=', 'od.id')
            ->join('order_parts as op', 'o.order_part_id', '=', 'op.id')
            ->selectRaw('YEAR(o.date) as tahun, SUM(od.labor_cost_service + (op.qty * op.part_price)) as total')
            ->groupBy('tahun')
            ->orderBy('tahun')
            ->pluck('total', 'tahun')
            ->toArray();

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
