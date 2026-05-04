<?php

namespace App\Filament\Resources\Sales\Widgets;

use App\Models\Sale;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SalesTableStats extends StatsOverviewWidget
{
    protected ?string $heading = 'Sales Snapshot';

    protected static bool $isLazy = false;

    protected function getStats(): array
    {
        $monthStart = now()->startOfMonth();
        $monthEnd = now()->endOfMonth();

        $dailyTrend = Sale::query()
            ->where('created_at', '>=', now()->subDays(13)->startOfDay())
            ->selectRaw('DATE(created_at) as d, SUM(total) as total')
            ->groupBy('d')
            ->orderBy('d')
            ->pluck('total')
            ->map(fn ($v) => (float) $v)
            ->values()
            ->all();

        return [
            Stat::make('Revenue (This Month)', 'TZS ' . number_format((float) Sale::whereBetween('created_at', [$monthStart, $monthEnd])->sum('total'), 0))
                ->description('Current month gross sales')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart($dailyTrend)
                ->color('success'),
            Stat::make('Sales Count', number_format((int) Sale::whereBetween('created_at', [$monthStart, $monthEnd])->count()))
                ->description('Transactions this month')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('info'),
            Stat::make('Average Ticket', 'TZS ' . number_format((float) Sale::whereBetween('created_at', [$monthStart, $monthEnd])->avg('total'), 0))
                ->description('Average per sale')
                ->descriptionIcon('heroicon-m-calculator')
                ->color('primary'),
        ];
    }
}

