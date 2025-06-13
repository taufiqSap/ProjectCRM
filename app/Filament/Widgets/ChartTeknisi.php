<?php

namespace App\Filament\Widgets;

use Illuminate\Support\Facades\DB;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
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

          if (!$start || !$end) {
            return [
                Stat::make('Total Transaction', '-'),
                Stat::make('Total Customer', '-'),
                Stat::make('Average Amount / Transaction', '-'),
            ];
        }

        // Subquery untuk ambil invoice unik per teknisi
        $subQuery = DB::table('orders as o')
            ->join('order_details as od', 'o.order_detail_id', '=', 'od.id')
            ->select('o.technician_id', 'od.no_invoice')
            ->when($start, fn ($q) => $q->whereDate('o.date', '>=', $start))
            ->when($end, fn ($q) => $q->whereDate('o.date', '<=', $end))
            ->groupBy('o.technician_id', 'od.no_invoice');

        // Bungkus sebagai subquery dan hitung jumlah invoice per teknisi
        $query = DB::table(DB::raw("({$subQuery->toSql()}) as unique_invoices"))
            ->mergeBindings($subQuery)
            ->join('technicians as t', 'unique_invoices.technician_id', '=', 't.id')
            ->select('t.name', DB::raw('COUNT(*) as jumlah_invoice'))
            ->groupBy('t.id', 't.name')
            ->orderByDesc('jumlah_invoice')
            ->limit(5);

        $data = $query->get();

        if ($data->isEmpty()) {
            return [
                'datasets' => [
                    [
                        'label' => 'Jumlah Invoice',
                        'data' => [],
                        'backgroundColor' => [],
                        'borderColor' => '#ffffff',
                    ],
                ],
                'labels' => [],
            ];
        }

        $labels = $data->pluck('name')->toArray();
        $values = $data->pluck('jumlah_invoice')->toArray();

        $colors = ['#60A5FA', '#34D399', '#FBBF24', '#A78BFA', '#F87171'];
        $backgroundColor = array_slice($colors, 0, count($values));

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Invoice',
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
