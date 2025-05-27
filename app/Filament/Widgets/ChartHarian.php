<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Forms\Components\Select;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class ChartHarian extends ChartWidget
{
    protected static ?string $heading = 'Penghasilan Harian';

    public array $filterFormData = [];

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $selectedYear = $this->filterFormData['year'] ?? date('Y');
        $selectedMonth = $this->filterFormData['month'] ?? date('n');

        $data = DB::table('orders')
            ->join('order_details', 'orders.order_detail_id', '=', 'order_details.id')
            ->selectRaw('DAY(orders.date) as day, SUM(order_details.labor_cost_service) as total')
            ->whereYear('orders.date', $selectedYear)
            ->whereMonth('orders.date', $selectedMonth)
            ->groupBy(DB::raw('DAY(orders.date)'))
            ->orderBy(DB::raw('DAY(orders.date)'))
            ->pluck('total', 'day');

        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $selectedMonth, $selectedYear);
        $penghasilan = [];

        for ($i = 1; $i <= $daysInMonth; $i++) {
            $penghasilan[] = $data[$i] ?? 0;
        }

        return [
            'datasets' => [
                [
                    'label' => "Penghasilan Harian ($selectedYear-" . str_pad($selectedMonth, 2, '0', STR_PAD_LEFT) . ")",
                    'data' => $penghasilan,
                    'borderColor' => '#22c55e',
                    'backgroundColor' => 'rgba(34, 197, 94, 0.2)',
                ],
            ],
            'labels' => range(1, $daysInMonth),
        ];
    }

    protected function getFormSchema(): array
    {
        $yearsList = Order::selectRaw('YEAR(date) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->toArray();

        $months = [
            '1' => 'Januari', '2' => 'Februari', '3' => 'Maret', '4' => 'April',
            '5' => 'Mei', '6' => 'Juni', '7' => 'Juli', '8' => 'Agustus',
            '9' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember',
        ];

        return [
            \Filament\Forms\Components\Group::make()
                ->statePath('filterFormData')
                ->schema([
                    Select::make('year')
                        ->label('Tahun')
                        ->options(array_combine($yearsList, $yearsList))
                        ->default($yearsList[0] ?? date('Y'))
                        ->reactive()
                        ->afterStateUpdated(fn () => $this->updateChartData()),

                    Select::make('month')
                        ->label('Bulan')
                        ->options($months)
                        ->default(date('n'))
                        ->reactive()
                        ->afterStateUpdated(fn () => $this->updateChartData()),
                ]),
        ];
    }

    public function mount(): void
    {
        parent::mount();

        $this->filterFormData['year'] = date('Y');
        $this->filterFormData['month'] = date('n');
    }

    public function updated($propertyName)
    {
        if (in_array($propertyName, ['filterFormData.year', 'filterFormData.month'])) {
            $this->updateChartData();
        }
    }

    protected function filtersFormColumns(): int
    {
        return 2;
    }
}
