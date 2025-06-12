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
        


        $uniqueInvoices = DB::table('orders')
        ->join('order_details', 'orders.order_detail_id', '=', 'order_details.id')
        ->whereBetween('orders.date', [$start, $end])
        ->selectRaw('order_details.no_invoice, order_details.total_amount, MONTH(orders.date) as month')
        ->groupBy('order_details.no_invoice', 'order_details.total_amount', DB::raw('MONTH(orders.date)'));

    // Step 2: Bungkus sebagai subquery dan hitung jumlah total_amount per bulan
    $query = DB::table(DB::raw("({$uniqueInvoices->toSql()}) as invoice_summary"))
        ->mergeBindings($uniqueInvoices) // penting agar bindings dari subquery ikut
        ->selectRaw('month, SUM(total_amount) as total')
        ->groupBy('month')
        ->orderBy('month');


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