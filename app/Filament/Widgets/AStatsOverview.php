<?php

namespace App\Filament\Widgets;

use App\Models\Customer;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class AStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        // Ambil filter tanggal dari session jika ada
        $start = session('dashboard_start_date');
        $end = session('dashboard_end_date');

        // Hitung total transaksi unik berdasarkan no_invoice yang benar-benar digunakan (join orders)
        $totalTransaksi = DB::table('orders as o')
            ->join('order_details as od', 'o.order_detail_id', '=', 'od.id')
            ->when($start, fn($q) => $q->whereDate('o.date', '>=', $start))
            ->when($end, fn($q) => $q->whereDate('o.date', '<=', $end))
            ->select('od.no_invoice')
            ->distinct()
            ->count('od.no_invoice');

        // Hitung total customer unik (berdasarkan nama saja)
        $totalCustomer = Customer::distinct('name')->count('name');

        // Hitung average amount per transaksi (yang benar-benar digunakan dalam orders)
        $averageAmount = DB::table('orders as o')
            ->join('order_details as od', 'o.order_detail_id', '=', 'od.id')
            ->when($start, fn($q) => $q->whereDate('o.date', '>=', $start))
            ->when($end, fn($q) => $q->whereDate('o.date', '<=', $end))
            ->groupBy('od.no_invoice', 'od.total_amount')
            ->select('od.total_amount')
            ->get()
            ->avg('total_amount');

        return [
            Stat::make('Total Transaction', number_format($totalTransaksi, 0, ',', '.')),
            Stat::make('Total Customer', number_format($totalCustomer, 0, ',', '.')),
            Stat::make('Average Amount / Transaction', 'Rp' . number_format($averageAmount, 0, ',', '.')),
        ];
    }
}
