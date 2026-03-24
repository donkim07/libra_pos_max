<?php

namespace App\Filament\Widgets;

use App\Models\Sale;
use Filament\Tables;
use App\Models\Expense;
use App\Models\Purchase;
use Filament\Tables\Table;
use App\Models\Manufacturing;
use Illuminate\Support\Carbon;
use Filament\Widgets\TableWidget;
// use Illuminate\Support\Collection;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class ProfitLossBreakdownTable extends TableWidget
{
    use InteractsWithPageFilters;

    protected static bool $isLazy = false;
    protected static bool $isDiscovered = false;
    protected int | string | array $columnSpan = 'full';

    protected $listeners = ['filtersUpdated' => '$refresh'];

    protected function getTableHeading(): ?string
    {
        return 'Financial Breakdown';
    }

    public function table(Table $table): Table
    {
        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;

        $start = $startDate ? Carbon::parse($startDate)->startOfDay() : now()->subMonth()->startOfDay();
        $end = $endDate ? Carbon::parse($endDate)->endOfDay() : now()->endOfDay();

        // Build the breakdown data
        $breakdown = collect([
            [
                'category' => 'Revenue',
                'subcategory' => 'Sales',
                'amount' => Sale::whereBetween('created_at', [$start, $end])->sum('total'),
                'type' => 'income',
            ],
            [
                'category' => 'Cost of Goods Sold',
                'subcategory' => 'Purchases',
                'amount' => Purchase::whereBetween('created_at', [$start, $end])->sum('total'),
                'type' => 'expense',
            ],
            [
                'category' => 'Cost of Goods Sold',
                'subcategory' => 'Manufacturing Costs',
                'amount' => Manufacturing::whereBetween('date_manufactured', [$start, $end])->sum('total_cost'),
                'type' => 'expense',
            ],
            [
                'category' => 'Operating Expenses',
                'subcategory' => 'General Expenses',
                'amount' => Expense::whereBetween('incurred_on', [$start, $end])->sum('amount'),
                'type' => 'expense',
            ],
        ]);

        return $table
            ->query(
                // Create a fake query builder with our collection
                Sale::query()->whereRaw('1 = 0')
            )
            ->columns([
                TextColumn::make('category')
                    ->label('Category')
                    ->weight('bold'),

                TextColumn::make('subcategory')
                    ->label('Subcategory'),

                TextColumn::make('amount')
                    ->label('Amount')
                    ->money('tzs')
                    ->color(fn ($record) => $record->type === 'income' ? 'success' : 'danger')
                    ->weight('semibold'),

                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'income' => 'success',
                        'expense' => 'danger',
                    }),
            ])
            ->paginated(false)
            ->recordClasses(fn ($record) => $record->category === 'Revenue' ? 'bg-success-50' : '')
            // Override the query to use our collection
            ->modifyQueryUsing(function () use ($breakdown) {
                return $breakdown;
            });
    }

    public function getTableRecords(): \Illuminate\Support\Collection
    {
        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;

        $start = $startDate ? Carbon::parse($startDate)->startOfDay() : now()->subMonth()->startOfDay();
        $end = $endDate ? Carbon::parse($endDate)->endOfDay() : now()->endOfDay();

        return collect([
            (object) [
                'category' => 'Revenue',
                'subcategory' => 'Sales',
                'amount' => Sale::whereBetween('created_at', [$start, $end])->sum('total'),
                'type' => 'income',
            ],
            (object) [
                'category' => 'Cost of Goods Sold',
                'subcategory' => 'Purchases',
                'amount' => Purchase::whereBetween('created_at', [$start, $end])->sum('total'),
                'type' => 'expense',
            ],
            (object) [
                'category' => 'Cost of Goods Sold',
                'subcategory' => 'Manufacturing Costs',
                'amount' => Manufacturing::whereBetween('date_manufactured', [$start, $end])->sum('total_cost'),
                'type' => 'expense',
            ],
            (object) [
                'category' => 'Operating Expenses',
                'subcategory' => 'General Expenses',
                'amount' => Expense::whereBetween('incurred_on', [$start, $end])->sum('amount'),
                'type' => 'expense',
            ],
        ]);
    }
}
