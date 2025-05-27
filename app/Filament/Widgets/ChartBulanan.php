<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Forms\Components\Select;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class ChartBulanan extends ChartWidget
{
    protected static ?string $heading = 'Penghasilan PerBulan';
    protected static ?int $sort = 1;

    public array $filterFormData = [];

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $selectedYear = $this->filterFormData['year'] ?? null;

        // Ambil daftar tahun valid dari kolom 'date'
        $validYears = Order::whereNotNull('date')
            ->selectRaw('YEAR(date) as year')
            ->distinct()
            ->pluck('year')
            ->toArray();

        if (!in_array((int) $selectedYear, $validYears)) {
            $selectedYear = $validYears[0] ?? date('Y');
        }

        $selectedMonth = $this->filterFormData['month'] ?? null;

        if ($selectedMonth) {
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
                        'borderColor' => '#10B981',
                        'backgroundColor' => 'rgba(16, 185, 129, 0.2)',
                    ],
                ],
                'labels' => array_map(fn ($i) => strval($i), range(1, $daysInMonth)),
            ];
        } else {
            $data = DB::table('orders')
                ->join('order_details', 'orders.order_detail_id', '=', 'order_details.id')
                ->selectRaw('MONTH(orders.date) as month, SUM(order_details.labor_cost_service) as total')
                ->whereYear('orders.date', $selectedYear)
                ->groupBy(DB::raw('MONTH(orders.date)'))
                ->orderBy(DB::raw('MONTH(orders.date)'))
                ->pluck('total', 'month');

            $penghasilan = [];

            for ($i = 1; $i <= 12; $i++) {
                $penghasilan[] = $data[$i] ?? 0;
            }

            return [
                'datasets' => [
                    [
                        'label' => "Penghasilan Bulanan ($selectedYear)",
                        'data' => $penghasilan,
                        'borderColor' => '#3B82F6',
                        'backgroundColor' => 'rgba(59, 130, 246, 0.2)',
                    ],
                ],
                'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            ];
        }
    }

    protected function getFormSchema(): array
    {
        // Ambil semua tahun dari kolom 'date' yang tidak null
        $yearsList = Order::whereNotNull('date')
            ->selectRaw('YEAR(date) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->toArray();

        $years = array_combine($yearsList, $yearsList);
        $defaultYear = $yearsList[0] ?? date('Y');

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
                        ->options($years)
                        ->default($defaultYear)
                        ->reactive()
                        ->afterStateUpdated(fn () => $this->updateChartData()),

                    Select::make('month')
                        ->label('Bulan (Opsional)')
                        ->options($months)
                        ->placeholder('Semua Bulan')
                        ->reactive()
                        ->afterStateUpdated(fn () => $this->updateChartData()),
                ]),
        ];
    }

    public function mount(): void
    {
        parent::mount();

        $yearsList = Order::whereNotNull('date')
            ->selectRaw('YEAR(date) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->toArray();

        $defaultYear = $yearsList[0] ?? date('Y');

        $this->filterFormData['year'] = $defaultYear;
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
