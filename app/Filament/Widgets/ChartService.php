<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class ChartService extends ChartWidget
{
    protected static ?string $heading = 'Jumlah Service per Bulan';

    protected function getData(): array
    {
        // Gunakan tabel 'orders' sesuai ERD
        $serviceData = DB::table('orders')
            ->selectRaw('MONTH(date) as month, COUNT(*) as total')
            ->groupByRaw('MONTH(date)')
            ->orderByRaw('MONTH(date)')
            ->pluck('total', 'month');

        $jumlahService = [];
        for ($i = 1; $i <= 12; $i++) {
            $jumlahService[] = $serviceData[$i] ?? 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Service',
                    'data' => $jumlahService,
                    'backgroundColor' => '#4F46E5',
                    'borderColor' => '#6366F1',
                ],
            ],
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
