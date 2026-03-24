<?php

namespace App\Filament\Pages;

use UnitEnum;
use BackedEnum;
use Filament\Schemas\Schema;
use Illuminate\Contracts\View\View;
use App\Filament\Widgets\StatsOverview;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\DatePicker;
use App\Filament\Widgets\MonthlySalesChart;
use App\Filament\Widgets\TopSoldItemsChart;
use App\Filament\Widgets\LowStockItemsTable;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Schemas\Components\Utilities\Get;
use App\Filament\Widgets\ExpensesBreakdownChart;
use Filament\Pages\Dashboard\Actions\FilterAction;
use App\Filament\Widgets\TopManufacturedItemsChart;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class Dashboard extends BaseDashboard
{
    use HasFiltersForm;

    use HasPageShield;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-home';

    protected static ?string $title = 'Dashboard';



        // protected string $view = 'filament.pages.dashboard';

    // Responsive grid: 1 column mobile, 2 md, 3 xl
    public function getColumns(): int | array
    {

        return [
            // 2,
            'md' => 2,
            'xl' => 2,
        ];
    }


    // Add date filters (optional, for charts/stats over time)
public function filtersForm(Schema $schema): Schema
{
    return $schema
    ->schema([
    Section::make('')
        ->schema([
            DatePicker::make('start_date')
                ->label('From')
                ->live()
                ->default(now()->subDays(30))
                ->maxDate(fn (Get $get) => $get('end_date') ?: now()),

            DatePicker::make('end_date')
                ->label('To')
                ->live()
                ->default(now())
                ->minDate(fn (Get $get) => $get('start_date'))
                ->maxDate(now()),
        ])
        ->columnSpanFull()
        ->columns(2), // Display side by side
    ]);
}






    // protected function getHeaderWidgets(): array
    // {
    //     return [
    //         StatsOverview::class,
    //         TopSoldItemsChart::class,
    //     TopManufacturedItemsChart::class,
    //     ExpensesBreakdownChart::class,
    //         MonthlySalesChart::class,
    //         LowStockItemsTable::class,
    //         // Add more: e.g. TopPurchasedItemsChart, ManufacturingOutputChart
    //     ];
    // }
}
