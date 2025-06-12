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

        // Subquery untuk mengambil invoice unik per tahun
        $subQuery = DB::table('orders as o')
            ->join('order_details as od', 'o.order_detail_id', '=', 'od.id')
            ->selectRaw('YEAR(o.date) as year, od.no_invoice')
            ->when($start, fn($q) => $q->whereDate('o.date', '>=', $start))
            ->when($end, fn($q) => $q->whereDate('o.date', '<=', $end))
            ->groupBy('year', 'od.no_invoice');

        // Hitung jumlah invoice unik per tahun dari subquery
        $query = DB::table(DB::raw("({$subQuery->toSql()}) as unique_invoices"))
            ->mergeBindings($subQuery)
            ->selectRaw('year, COUNT(*) as total')
            ->groupBy('year')
            ->orderBy('year');

        $data = $query->pluck('total', 'year');

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
        return 'bar'; // Ganti ke 'line' jika ingin tampilan garis
    }
}

