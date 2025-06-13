<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
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

          if (!$start || !$end) {
            return [
                Stat::make('Total Transaction', '-'),
                Stat::make('Total Customer', '-'),
                Stat::make('Average Amount / Transaction', '-'),
            ];
        }

        // Subquery untuk mengambil invoice unik per tanggal
        $uniqueInvoices = DB::table('orders as o')
            ->join('order_details as od', 'o.order_detail_id', '=', 'od.id')
            ->selectRaw('od.no_invoice, DATE(o.date) as tanggal, od.total_amount')
            ->when($start, fn($q) => $q->whereDate('o.date', '>=', $start))
            ->when($end, fn($q) => $q->whereDate('o.date', '<=', $end))
            ->groupBy('od.no_invoice', 'od.total_amount', DB::raw('DATE(o.date)'));

        // Bungkus sebagai subquery dan jumlahkan total_amount per tanggal
        $query = DB::table(DB::raw("({$uniqueInvoices->toSql()}) as invoice_summary"))
            ->mergeBindings($uniqueInvoices)
            ->selectRaw('tanggal, SUM(total_amount) as total')
            ->groupBy('tanggal')
            ->orderBy('tanggal');

        $data = $query->pluck('total', 'tanggal');

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
