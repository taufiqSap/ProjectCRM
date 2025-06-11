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

        $query = DB::table('orders')
            ->selectRaw('MONTH(date) as month, COUNT(*) as total');

        if ($start) $query->whereDate('date', '>=', $start);
        if ($end) $query->whereDate('date', '<=', $end);

        $data = $query->groupByRaw('MONTH(date)')
            ->orderByRaw('MONTH(date)')
            ->pluck('total', 'month');

        $result = [];
        for ($i = 1; $i <= 12; $i++) {
            $result[] = $data[$i] ?? 0;
        }

        return [
            'datasets' => [[ 'data' => $result ]],
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}