<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class ChartBulanan extends ChartWidget
{
    protected static ?string $heading = 'Penghasilan Bulanan';

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $start = session('dashboard_start_date') ?? now()->startOfMonth()->toDateString();
        $end = session('dashboard_end_date') ?? now()->endOfMonth()->toDateString();

        $query = DB::table('orders')
            ->join('order_details', 'orders.order_detail_id', '=', 'order_details.id')
            ->join('order_parts as op', 'orders.order_part_id', '=', 'op.id')
            ->selectRaw('MONTH(orders.date) as month, SUM(order_details.labor_cost_service + (op.qty * op.part_price)) as total')
            ->whereDate('orders.date', '>=', $start)
            ->whereDate('orders.date', '<=', $end)
            ->groupByRaw('MONTH(orders.date)')
            ->orderByRaw('MONTH(orders.date)');

        $data = $query->pluck('total', 'month');

        $result = [];
        for ($i = 1; $i <= 12; $i++) {
            $result[] = $data[$i] ?? 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Penghasilan',
                    'data' => $result,
                    'backgroundColor' => '#4f46e5',
                    'borderColor' => '#4f46e5',
                    'tension' => 0.1
                ]
            ],
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
        ];
    }

    public static function canView(): bool
    {
        return true;
    }
}