<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class ChartService extends ChartWidget
{
    protected static ?string $heading = 'Jumlah Service per Bulan';

    protected function getData(): array
    {
        $start = session('dashboard_start_date');
        $end = session('dashboard_end_date');

        // Subquery: Ambil invoice unik per bulan
        $subQuery = DB::table('orders as o')
            ->join('order_details as od', 'o.order_detail_id', '=', 'od.id')
            ->selectRaw('MONTH(o.date) as month, od.no_invoice')
            ->when($start, fn($q) => $q->whereDate('o.date', '>=', $start))
            ->when($end, fn($q) => $q->whereDate('o.date', '<=', $end))
            ->groupBy('month', 'od.no_invoice');

        // Bungkus subquery, hitung jumlah invoice unik per bulan
        $query = DB::table(DB::raw("({$subQuery->toSql()}) as unique_invoices"))
            ->mergeBindings($subQuery)
            ->selectRaw('month, COUNT(*) as total')
            ->groupBy('month')
            ->orderBy('month');

        $data = $query->pluck('total', 'month');

        // Buat array hasil lengkap 12 bulan
        $result = [];
        for ($i = 1; $i <= 12; $i++) {
            $result[] = $data[$i] ?? 0;
        }

        return [
            'datasets' => [[ 'data' => $result, 'label' => 'Jumlah Service / Bulan' ]],
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}