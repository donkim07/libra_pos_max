<?php

namespace App\Filament\Widgets;

use App\Models\Sale;
use Flowframe\Trend\Trend;
use Filament\Schemas\Schema;
use Illuminate\Support\Carbon;
use Flowframe\Trend\TrendValue;
use Filament\Widgets\ChartWidget;
use Filament\Forms\Components\DatePicker;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\ChartWidget\Concerns\HasFiltersSchema;
use Filament\Support\RawJs;

class MonthlySalesChart extends ChartWidget
{
    use InteractsWithPageFilters;
    // use HasFiltersSchema;
    use HasWidgetShield;

    protected static ?int $sort = 2;

    protected static bool $isLazy = false;
        protected int | string | array $columnSpan = 1;

    protected ?string $heading = 'Monthly Sales Revenue';

    protected ?string $pollingInterval = null; // Disable polling for smoothness

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $start = Carbon::parse($this->filters['start_date'] ?? Carbon::now()->subYear());
        $end = Carbon::parse($this->filters['end_date'] ?? Carbon::now());

        $data = Trend::model(Sale::class)
            ->between(start: $start, end: $end)
            ->perMonth()
            ->sum('total');

        return [
            'datasets' => [
                [
                    'label'           => 'Sales Revenue (TZS)',
                    'data'            => $data->map(fn (TrendValue $value) => $value->aggregate),
                    'backgroundColor' => 'rgba(54, 162, 235, 0.1)', // Semi-transparent fill for smooth look
                    'borderColor'     => '#36A2EB',
                    'borderWidth'     => 3,
                    'pointBackgroundColor' => '#36A2EB',
                    'pointBorderColor' => '#fff',
                    'pointBorderWidth' => 2,
                    'pointRadius'     => 5,
                    'fill'            => true, // Smooth fill under line
                ],
            ],
            'labels' => $data->map(fn (TrendValue $value) => Carbon::parse($value->date)->format('M Y')),
        ];
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => ['display' => true],
                'tooltip' => [
                    'mode' => 'index', // Smooth crosshair on hover
                    'intersect' => false,
                ],
            ],
            'interaction' => [
                'mode' => 'nearest',
                'axis' => 'x',
                'intersect' => false,
            ],
            'scales' => [
                'x' => [
                    'grid' => ['display' => false], // Cleaner, smoother grid
                ],
                'y' => [
                    'beginAtZero' => true,
                ],
            ],
            'animation' => [
                'duration' => 2000, // 2s smooth entry
                'easing' => 'easeInOutQuart', // Buttery smooth curve
                'delay' => 500, // Stagger start
            ],
            'elements' => [
                'line' => [
                    'tension' => 0.4, // Smooth curve (not straight lines)
                ],
                'point' => [
                    'hoverRadius' => 8, // Smooth hover growth
                ],
            ],

        ];
    }
}
