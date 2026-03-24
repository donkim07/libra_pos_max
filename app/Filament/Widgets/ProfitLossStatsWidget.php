<?php

namespace App\Filament\Widgets;

use App\Models\Sale;
use App\Models\Expense;
use App\Models\Purchase;
use Illuminate\Support\Carbon;
use App\Models\Manufacturing;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class ProfitLossStatsWidget extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static bool $isLazy = false;
    protected static bool $isDiscovered = false;

    protected $listeners = ['filtersUpdated' => '$refresh'];

    protected function getStats(): array
    {
        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;

        $start = $startDate ? Carbon::parse($startDate)->startOfDay() : now()->subMonth()->startOfDay();
        $end = $endDate ? Carbon::parse($endDate)->endOfDay() : now()->endOfDay();

        // Calculate all financial metrics
        $revenue = Sale::whereBetween('created_at', [$start, $end])->sum('total');

        $purchaseCosts = Purchase::whereBetween('created_at', [$start, $end])->sum('total');
        $manufacturingCosts = Manufacturing::whereBetween('date_manufactured', [$start, $end])->sum('total_cost');
        $cogs = $purchaseCosts + $manufacturingCosts;

        $expenses = Expense::whereBetween('incurred_on', [$start, $end])->sum('amount');

        $grossProfit = $revenue - $cogs;
        $grossMargin = $revenue > 0 ? ($grossProfit / $revenue) * 100 : 0;

        $netProfit = $grossProfit - $expenses;
        $netMargin = $revenue > 0 ? ($netProfit / $revenue) * 100 : 0;

        return [
            Stat::make('Total Revenue', 'TZS ' . number_format($revenue, 0))
                ->description('Sales during period')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success')
                ->chart($this->getRevenueChart($start, $end)),

            Stat::make('Cost of Goods Sold', 'TZS ' . number_format($cogs, 0))
                ->description('Purchases: TZS ' . number_format($purchaseCosts, 0) . ' | Manufacturing: TZS ' . number_format($manufacturingCosts, 0))
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('danger'),

            Stat::make('Gross Profit', 'TZS ' . number_format($grossProfit, 0))
                ->description(number_format($grossMargin, 1) . '% margin')
                ->descriptionIcon($grossProfit >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($grossProfit >= 0 ? 'primary' : 'danger'),

            Stat::make('Operating Expenses', 'TZS ' . number_format($expenses, 0))
                ->description('Period expenses')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('warning'),

            Stat::make('Net Profit / Loss', 'TZS ' . number_format($netProfit, 0))
                ->description(number_format($netMargin, 1) . '% net margin')
                ->descriptionIcon($netProfit >= 0 ? 'heroicon-m-check-circle' : 'heroicon-m-x-circle')
                ->color($netProfit >= 0 ? 'success' : 'danger')
                ->extraAttributes([
                    'class' => 'ring-2 ' . ($netProfit >= 0 ? 'ring-success-500' : 'ring-danger-500'),
                ]),
        ];
    }

    protected function getRevenueChart(Carbon $start, Carbon $end): array
    {
        // Simple daily revenue chart for the period
        $days = $start->diffInDays($end);

        if ($days > 30) {
            // Monthly aggregation for longer periods
            return [];
        }

        $data = [];
        for ($i = 0; $i <= min($days, 7); $i++) {
            $date = $start->copy()->addDays($i);
            $revenue = Sale::whereDate('created_at', $date)->sum('total');
            $data[] = $revenue;
        }

        return $data;
    }
}
