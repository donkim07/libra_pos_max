<?php

namespace App\Filament\Widgets;

use App\Models\Sale;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class TotalSalesStats extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static bool $isDiscovered = false;
    protected static bool $isLazy = false;

    protected $listeners = ['filtersUpdated' => '$refresh'];

    protected function getStats(): array
    {
        $startDate = $this->filters['startDate'] ?? null;
        $endDate   = $this->filters['endDate']   ?? null;
        $storeId   = $this->filters['storeId']   ?? null;

        $query = Sale::query();

        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }
        if ($storeId) {
            $query->where('store_id', $storeId);
        }

        $totalRevenue = $query->sum('total');
        $totalSales   = $query->count();
        $avgSale      = $totalSales ? $totalRevenue / $totalSales : 0;

        // Sparkline: monthly sales trend (last 12 months)
        $salesTrend = Sale::query()
            ->when($storeId, fn($q) => $q->where('store_id', $storeId))
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, SUM(total) as value")
            ->where('created_at', '>=', now()->subMonths(11))
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('value')
            ->toArray();

        return [
            Stat::make('Total Revenue', 'TZS ' . number_format($totalRevenue, 0))
                ->description('Selected period')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart($salesTrend)
                ->color('success'),

            Stat::make('Number of Sales', number_format($totalSales))
                ->description('Transactions')
                ->descriptionIcon('heroicon-o-shopping-cart')
                ->color('primary'),

            Stat::make('Average Sale Value', 'TZS ' . number_format($avgSale, 0))
                ->description('Per transaction')
                ->descriptionIcon('heroicon-o-calculator')
                ->color('warning'),
        ];
    }
}
