<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class ChartHarian extends ChartWidget
{
    protected static ?string $heading = 'Penghasilan Harian';

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $start = session('dashboard_start_date') ?? now()->startOfMonth()->toDateString();
        $end = session('dashboard_end_date') ?? now()->endOfMonth()->toDateString();

        // Ambil tanggal dalam rentang
        $dates = collect();
        $current = \Carbon\Carbon::parse($start);
        $last = \Carbon\Carbon::parse($end);

        while ($current <= $last) {
            $dates->push($current->toDateString());
            $current->addDay();
        }

        // Query data harian
        $data = DB::table('orders')
            ->join('order_details', 'orders.order_detail_id', '=', 'order_details.id')
            ->selectRaw('DATE(orders.date) as date, SUM(order_details.labor_cost_service) as total')
            ->whereDate('orders.date', '>=', $start)
            ->whereDate('orders.date', '<=', $end)
            ->groupByRaw('DATE(orders.date)')
            ->pluck('total', 'date');

        // Susun hasil sesuai tanggal
        $result = [];
        foreach ($dates as $date) {
            $result[] = $data[$date] ?? 0;
        }

        return [
            'datasets' => [[ 'data' => $result ]],
            'labels' => $dates->map(fn($d) => \Carbon\Carbon::parse($d)->format('d M'))->toArray(),
        ];
    }
}