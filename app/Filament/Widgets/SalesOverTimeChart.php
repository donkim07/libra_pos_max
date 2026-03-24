<?php

namespace App\Filament\Widgets;

use App\Models\Sale;
use Flowframe\Trend\Trend;
use Illuminate\Support\Carbon;
use Flowframe\Trend\TrendValue;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class SalesOverTimeChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected ?string $heading = 'Sales Over Time';
    protected static bool $isLazy = false;
    protected static bool $isDiscovered = false;
    protected int | string | array $columnSpan = 'full';

    protected $listeners = ['filtersUpdated' => '$refresh'];

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;

        $start = $startDate ? Carbon::parse($startDate) : now()->startOfYear();
        $end = $endDate ? Carbon::parse($endDate) : now()->endOfYear();

        $trend = Trend::model(Sale::class)
            ->between(start: $start, end: $end)
            ->perWeek()
            ->sum('total');

        return [
            'datasets' => [
                [
                    'label' => 'Revenue',
                    'data' => $trend->map(fn (TrendValue $value) => $value->aggregate),
                    'borderColor' => '#10b981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'fill' => true,
                ],
            ],
            'labels' => $trend->map(fn (TrendValue $value) => $value->date),
        ];
    }

    protected function getOptions(): array
    {
        return [
            // 'maintainAspectRatio' => false,
            // 'aspectRatio' => 1, // Add this to control height
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'precision' => 0,
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display' => true,
                ],
            ],
        ];
    }
}
