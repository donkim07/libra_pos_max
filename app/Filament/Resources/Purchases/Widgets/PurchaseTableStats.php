<?php

namespace App\Filament\Resources\Purchases\Widgets;

use App\Models\Purchase;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PurchaseTableStats extends StatsOverviewWidget
{
    protected ?string $heading = 'Purchases Snapshot';

    protected static bool $isLazy = false;

    protected function getStats(): array
    {
        $monthStart = now()->startOfMonth();
        $monthEnd = now()->endOfMonth();

        $dailyTrend = Purchase::query()
            ->where('created_at', '>=', now()->subDays(13)->startOfDay())
            ->selectRaw('DATE(created_at) as d, SUM(total) as total')
            ->groupBy('d')
            ->orderBy('d')
            ->pluck('total')
            ->map(fn ($v) => (float) $v)
            ->values()
            ->all();

        return [
            Stat::make('Spend (This Month)', 'TZS ' . number_format((float) Purchase::whereBetween('created_at', [$monthStart, $monthEnd])->sum('total'), 0))
                ->description('Current month purchasing')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->chart($dailyTrend)
                ->color('warning'),
            Stat::make('Purchase Orders', number_format((int) Purchase::whereBetween('created_at', [$monthStart, $monthEnd])->count()))
                ->description('Created this month')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('info'),
            Stat::make('Average PO', 'TZS ' . number_format((float) Purchase::whereBetween('created_at', [$monthStart, $monthEnd])->avg('total'), 0))
                ->description('Average value per purchase')
                ->descriptionIcon('heroicon-m-calculator')
                ->color('primary'),
        ];
    }
}

