<?php

namespace App\Filament\Widgets;

use App\Models\Item;
use App\Models\Sale;
use App\Models\Purchase;
use App\Models\Manufacturing;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Filament\Widgets\StatsOverviewWidget\Stat;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class StatsOverview extends BaseWidget
{
    use InteractsWithPageFilters;
    use HasWidgetShield;

    protected static ?int $sort = 1;

    protected static bool $isLazy = false;

    protected function getStats(): array
    {
        $start = isset($this->filters['start_date'])
    ? Carbon::parse($this->filters['start_date'])->startOfDay()
    : now()->subMonth();

$end = isset($this->filters['end_date'])
    ? Carbon::parse($this->filters['end_date'])->endOfDay()
    : now();

        // Helper: get monthly totals for sparkline (last 6–12 months for smooth chart)
        $getTrend = function ($model, $column = 'total', $dateColumn = 'created_at') {
            return $model::query()
                ->whereBetween($dateColumn, [now()->subMonths(11), now()])
                ->selectRaw("DATE_FORMAT($dateColumn, '%Y-%m') as month, SUM($column) as value")
                ->groupBy('month')
                ->orderBy('month')
                ->pluck('value')
                ->toArray();
        };

        // Sales revenue trend
        $salesTrend = $getTrend(Sale::class, 'total');

        // Purchases trend
        $purchasesTrend = $getTrend(Purchase::class, 'total');

        // Manufactured qty trend (using quantity column)
        $manufacturedTrend = $getTrend(Manufacturing::class, 'quantity');

        return [
            Stat::make('Total Sales Revenue', 'TZS ' . number_format(
                Sale::whereBetween('created_at', [$start, $end])->sum('total'),
                0
            ))
                ->description('From ' . $start->format('M d') . ' to ' . $end->format('M d'))
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart($salesTrend)           // ← sparkline
                ->color('success'),

            Stat::make('Total Purchases', 'TZS ' . number_format(
                Purchase::whereBetween('created_at', [$start, $end])->sum('total'),
                0
            ))
                ->description('Expenditure overview')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->chart($purchasesTrend)       // ← sparkline
                ->color('danger'),

            Stat::make('Manufactured Items', Manufacturing::whereBetween('created_at', [$start, $end])->sum('quantity'))
                ->description('Total units produced')
                ->descriptionIcon('heroicon-m-cog')
                ->chart($manufacturedTrend)    // ← sparkline
                ->color('primary'),

            Stat::make('Low Stock Items', Item::where('item_type', 'inventory')
                ->select('items.*')
                ->selectSub(
                    function ($q) {
                        $q->selectRaw('COALESCE(SUM(item_store.quantity), 0)')
                          ->from('item_store')
                          ->whereColumn('item_store.item_id', 'items.id');
                    },
                    'total_stock'
                )
                ->having('total_stock', '<', 5)
                ->count())
                ->description('< 5 units total across all stores')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('warning'),
                // No chart here — low stock is a count, not time series
        ];
    }
}
