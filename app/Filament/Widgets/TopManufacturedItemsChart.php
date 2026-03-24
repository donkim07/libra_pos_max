<?php

namespace App\Filament\Widgets;

use Filament\Support\RawJs;
use Filament\Schemas\Schema;
use App\Models\Manufacturing;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use Filament\Forms\Components\DatePicker;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\ChartWidget\Concerns\HasFiltersSchema;

class TopManufacturedItemsChart extends ChartWidget
{
    use InteractsWithPageFilters;
    // use HasFiltersSchema;
        use HasWidgetShield;

    protected ?string $heading = 'Most Manufactured Items';

    protected static bool $isLazy = false;

    protected static ?int $sort = 3;

    protected function getType(): string
    {
        return 'pie';
    }

    //         public function filtersSchema(Schema $schema): Schema
    // {
    //     return $schema->components([
    //         DatePicker::make('start_date')
    //             ->default(now()->subDays(30)),
    //         DatePicker::make('end_date')
    //             ->default(now()),
    //     ]);
    // }

    protected function getData(): array
    {
        $start = $this->filters['start_date'] ?? now()->subYear();
        $end   = $this->filters['end_date']   ?? now();

        $data = Manufacturing::query()
    ->select('manufacturings.item_id', DB::raw('SUM(manufacturings.quantity) as total_produced'), 'items.name')
    ->join('items', 'manufacturings.item_id', '=', 'items.id')
    ->whereBetween('manufacturings.created_at', [$start, $end])
    ->groupBy('manufacturings.item_id', 'items.name')
    ->orderByDesc('total_produced')
    ->limit(8)
    ->get();

        return [
            'datasets' => [
                [
                    'label'           => 'Quantity Produced',
                    'data'            => $data->pluck('total_produced')->toArray(),
                    'backgroundColor' => [
                        '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF',
                        '#FF9F40', '#E7E9ED', '#C9CBCF'
                    ],
                ],
            ],
            'labels' => $data->pluck('name')->toArray(),
        ];
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => ['position' => 'right'],
                'title'  => ['display' => true, 'text' => 'Top Manufactured Items'],
                //
            ],
            'animation' => [
                'animateRotate' => true, // Smooth pie rotation
                'animateScale'  => true, // Scale in from center
                'duration'      => 2000,
                'easing'        => 'easeOutBounce', // Fun bouncy entry

            ],

            'elements' => [
                'arc' => [
                    'borderRadius' => 5, // Rounded slice edges
                ],
            ],
        ];
    }
}
