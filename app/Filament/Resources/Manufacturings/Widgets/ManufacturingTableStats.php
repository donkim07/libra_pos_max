<?php

namespace App\Filament\Resources\Manufacturings\Widgets;

use App\Models\Manufacturing;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ManufacturingTableStats extends StatsOverviewWidget
{
    protected ?string $heading = 'Manufacturing Snapshot';

    protected static bool $isLazy = false;

    protected function getStats(): array
    {
        $monthStart = now()->startOfMonth();
        $monthEnd = now()->endOfMonth();

        $trend = Manufacturing::query()
            ->where('created_at', '>=', now()->subDays(13)->startOfDay())
            ->selectRaw('DATE(created_at) as d, SUM(quantity) as qty')
            ->groupBy('d')
            ->orderBy('d')
            ->pluck('qty')
            ->map(fn ($v) => (float) $v)
            ->values()
            ->all();

        return [
            Stat::make('Units Produced (Month)', number_format((float) Manufacturing::whereBetween('created_at', [$monthStart, $monthEnd])->sum('quantity'), 2))
                ->description('Current month output')
                ->descriptionIcon('heroicon-m-cog-8-tooth')
                ->chart($trend)
                ->color('success'),
            Stat::make('Manufacturing Orders', number_format((int) Manufacturing::whereBetween('created_at', [$monthStart, $monthEnd])->count()))
                ->description('Created this month')
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color('info'),
            Stat::make('Total Cost (Month)', 'TZS ' . number_format((float) Manufacturing::whereBetween('created_at', [$monthStart, $monthEnd])->sum('total_cost'), 0))
                ->description('Cost of produced orders')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('primary'),
        ];
    }
}

