<?php

namespace App\Filament\Widgets;

use App\Models\OrderDetail;
use App\Models\Customer;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class AStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $totalTransaksi = OrderDetail::distinct('no_invoice')->count('no_invoice');
        $totalCustomer = Customer::distinct('name')->count('name');


        $averageAmount = DB::table(DB::raw("(SELECT DISTINCT no_invoice, total_amount FROM order_details) as unique_amount"))
    ->avg('total_amount');

        return [
            Stat::make('Total Transaction', number_format($totalTransaksi, 0, ',', '.')),
            Stat::make('Total Customer', number_format($totalCustomer, 0, ',', '.')),
            Stat::make('Average Amount / Transaction', 'Rp' . number_format($averageAmount, 0, ',', '.')),
        ];
    }
}
