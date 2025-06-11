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
        $start = session('dashboard_start_date');
        $end = session('dashboard_end_date');

        $query = DB::table('orders')
            ->join('order_details', 'orders.order_detail_id', '=', 'order_details.id')
            ->selectRaw('MONTH(orders.date) as month, SUM(order_details.labor_cost_service) as total');

        if ($start) $query->whereDate('orders.date', '>=', $start);
        if ($end) $query->whereDate('orders.date', '<=', $end);

        $data = $query->groupByRaw('MONTH(orders.date)')
            ->orderByRaw('MONTH(orders.date)')
            ->pluck('total', 'month');

        $result = [];
        for ($i = 1; $i <= 12; $i++) {
            $result[] = $data[$i] ?? 0;
        }

        return [
            'datasets' => [[ 'data' => $result ]],
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
        ];
    }
}
