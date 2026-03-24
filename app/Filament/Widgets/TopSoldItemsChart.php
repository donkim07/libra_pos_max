<?php

namespace App\Filament\Widgets;

use App\Models\SalesItem;
use Filament\Schemas\Schema;
use Illuminate\Support\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use Filament\Forms\Components\DatePicker;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\ChartWidget\Concerns\HasFiltersSchema;
use Filament\Support\RawJs;

class TopSoldItemsChart extends ChartWidget
{
    use InteractsWithPageFilters;
    // use HasFiltersSchema;
    use HasWidgetShield;

    protected ?string $heading = 'Top 10 Most Sold Items';

    protected static bool $isLazy = false;

    protected static ?int $sort = 2;

        protected int | string | array $columnSpan = 1;

    protected function getType(): string
    {
        return 'bar';
    }

   protected function getData(): array
{
    $start = Carbon::parse($this->filters['start_date'] ?? now()->subYear());
    $end   = Carbon::parse($this->filters['end_date'] ?? now());

    $topItems = SalesItem::query()
        ->select('sales_items.item_id', DB::raw('SUM(sales_items.quantity) as total_qty'), 'items.name')
        ->join('items', 'sales_items.item_id', '=', 'items.id')
        ->whereBetween('sales_items.created_at', [$start, $end])
        ->groupBy('sales_items.item_id', 'items.name')
        ->orderByDesc('total_qty')
        ->limit(10)
        ->get();

    // Truncate long names in PHP
    $labels = $topItems->pluck('name')->map(function ($name) {
        return strlen($name) > 35 ? substr($name, 0, 25) . '...' : $name;
    })->toArray();

    return [
        'datasets' => [
            [
                'label'           => 'Quantity Sold',
                'data'            => $topItems->pluck('total_qty')->toArray(),
                'backgroundColor' => [
                    '#4e79a7', '#f28e2b', '#e15759', '#76b7b2', '#59a14f',
                    '#edc948', '#b07aa1', '#ff9da7', '#9c755f', '#bab0ac'
                ],
                'borderRadius'    => 8,
                'borderSkipped'   => false,
            ],
        ],
        'labels' => $labels,
    ];
}

    protected function getOptions(): array
    {
        return [
            'indexAxis' => 'y',
            'maintainAspectRatio' => false, // Important for proper height
            'plugins' => [
                'legend' => ['display' => false],
                'title'  => ['display' => true, 'text' => 'Top 10 Most Sold Items'],
            ],
            'scales' => [
                'x' => [
                    'title' => ['display' => true, 'text' => 'Quantity Sold'],
                    'beginAtZero' => true,
                    'grid' => ['display' => false],
                ],
                'y' => [
                    'ticks' => [
                        'autoSkip' => false,


                    ],
                    'grid' => ['color' => 'rgba(0,0,0,0.1)'],
                ],
            ],
            'animation' => [
                'duration' => 1500,
                'easing' => 'easeOutQuart',
            ],
        ];
    }
}
