<?php

namespace App\Filament\Widgets;

use App\Models\Expense;
use Filament\Support\RawJs;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class ExpensesBreakdownChart extends ChartWidget
{
    use InteractsWithPageFilters;

        use HasWidgetShield;

    protected ?string $heading = 'Expenses Breakdown';

    protected static ?int $sort = 3;

    protected static bool $isLazy = false;

    // protected int | string | array $columnSpan = 2;

    protected function getType(): string
    {
        return 'polarArea';
    }

    protected function getData(): array
    {
        $start = $this->filters['start_date'] ?? now()->subYear();
        $end   = $this->filters['end_date']   ?? now();

        // Group by title (or you can group by account_id / category later)
        $data = Expense::query()
            ->select('title', DB::raw('SUM(amount) as total_amount'))
            ->whereBetween('incurred_on', [$start, $end])
            ->groupBy('title')
            ->orderByDesc('total_amount')
            ->limit(10)
            ->get();

        return [
            'datasets' => [
                [
                    'label'           => 'Amount (TZS)',
                    'data'            => $data->pluck('total_amount')->toArray(),
                    'backgroundColor' => [
                        'rgba(255, 99, 132, 0.6)',  'rgba(54, 162, 235, 0.6)',
                        'rgba(255, 206, 86, 0.6)',  'rgba(75, 192, 192, 0.6)',
                        'rgba(153, 102, 255, 0.6)', 'rgba(255, 159, 64, 0.6)',
                        'rgba(199, 199, 199, 0.6)', 'rgba(83, 102, 255, 0.6)',
                        'rgba(40, 159, 64, 0.6)',   'rgba(210, 199, 199, 0.6)',
                    ],
                    'borderColor'     => 'rgba(255,255,255,0.8)',
                    'borderWidth'     => 1,
                ],
            ],
            'labels' => $data->pluck('title')->toArray() ?: ['No expenses recorded'],
        ];
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => ['position' => 'right'],
                'title'  => ['display' => true, 'text' => 'Expenses by Category/Title'],

            ],
            'scales' => [
                'r' => [
                    'ticks' => ['backdropColor' => 'transparent'],
                    'grid' => ['color' => 'rgba(0,0,0,0.1)'], // Subtle radial grid
                ],
            ],
            'animation' => [
                'animateRotate' => true, // Smooth radial rotation
                'animateScale'  => true, // Expand from center
                'duration'      => 2500,
                'easing'        => 'easeInOutElastic', // Elastic bounce for fun

            ],
            'elements' => [
                'arc' => [
                    'borderRadius' => 10, // Rounded edges
                ],
            ],
        ];
    }
}
