<?php

namespace App\Filament\Resources\BulkManufacturings\Widgets;

use App\Models\BulkManufacturing;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class BulkManufacturingTableStats extends StatsOverviewWidget
{
    protected ?string $heading = 'Bulk Manufacturing Snapshot';
    
    protected static bool $isLazy = false;

    protected function getStats(): array
    {
        $openCount = BulkManufacturing::where('is_finished', false)->count();
        $remaining = (float) BulkManufacturing::where('is_finished', false)->sum('remaining_quantity');
        $waste = (float) BulkManufacturing::sum('waste_quantity');

        $trend = BulkManufacturing::query()
            ->where('created_at', '>=', now()->subDays(13)->startOfDay())
            ->selectRaw('DATE(created_at) as d, SUM(quantity) as qty')
            ->groupBy('d')
            ->orderBy('d')
            ->pluck('qty')
            ->map(fn ($v) => (float) $v)
            ->values()
            ->all();

        return [
            Stat::make('Open Batches', number_format($openCount))
                ->description('Not finished yet')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
            Stat::make('Remaining Base Qty', number_format($remaining, 4))
                ->description('Across open batches')
                ->descriptionIcon('heroicon-m-beaker')
                ->chart($trend)
                ->color('info'),
            Stat::make('Recorded Waste', number_format($waste, 4))
                ->description('All time')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger'),
        ];
    }
}

