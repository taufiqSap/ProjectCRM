<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Customer;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class AStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $totalTransaksi = Order::count();

        $totalCustomer = Customer::count();

        $averageAmount = DB::table('orders')
            ->join('order_details', 'orders.order_detail_id', '=', 'order_details.id')
            ->avg('order_details.labor_cost_service');

        return [
            Stat::make('Total Transaction', number_format($totalTransaksi, 0, ',', '.')),
            Stat::make('Total Customer', number_format($totalCustomer, 0, ',', '.')),
            Stat::make('Average Amount / Transaction', 'Rp' . number_format($averageAmount, 0, ',', '.')),
        ];
    }
}
