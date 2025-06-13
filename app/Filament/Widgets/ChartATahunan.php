<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class ChartATahunan extends ChartWidget
{
    protected static ?string $heading = 'Penghasilan Per Tahun';

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

        // Subquery: Ambil invoice unik dengan total_amount per tahun
        $uniqueInvoices = DB::table('orders as o')
            ->join('order_details as od', 'o.order_detail_id', '=', 'od.id')
            ->selectRaw('od.no_invoice, YEAR(o.date) as tahun, od.total_amount')
            ->when($start, fn($q) => $q->whereDate('o.date', '>=', $start))
            ->when($end, fn($q) => $q->whereDate('o.date', '<=', $end))
            ->groupBy('od.no_invoice', 'od.total_amount', DB::raw('YEAR(o.date)'));

        // Bungkus sebagai subquery dan jumlahkan per tahun
        $query = DB::table(DB::raw("({$uniqueInvoices->toSql()}) as invoice_summary"))
            ->mergeBindings($uniqueInvoices)
            ->selectRaw('tahun, SUM(total_amount) as total')
            ->groupBy('tahun')
            ->orderBy('tahun');

        $data = $query->pluck('total', 'tahun')->toArray();

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

        $labels = array_keys($data);   // tahun
        $values = array_values($data); // total per tahun

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



