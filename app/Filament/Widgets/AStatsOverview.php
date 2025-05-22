<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Transaction', '5.757k'),
            Stat::make('Total Customer', '2.737'),
            Stat::make('Average Amount / Transaction', 'Rp172,285'),
        ];
    }
}
