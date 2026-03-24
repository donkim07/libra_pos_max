<?php

namespace App\Filament\Widgets;

use App\Models\Expense;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Carbon;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class ExpenseBreakdownTable extends TableWidget
{
    use InteractsWithPageFilters;

    protected static bool $isLazy = false;
    protected static bool $isDiscovered = false;
    protected int | string | array $columnSpan = 'full';

    protected $listeners = ['filtersUpdated' => '$refresh'];

    protected function getTableHeading(): ?string
    {
        return 'Expense Breakdown by Category';
    }

    public function table(Table $table): Table
    {
        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;

        $start = $startDate ? Carbon::parse($startDate)->startOfDay() : now()->subMonth()->startOfDay();
        $end = $endDate ? Carbon::parse($endDate)->endOfDay() : now()->endOfDay();

        return $table
            ->query(
                Expense::query()
                    ->selectRaw('category, COUNT(*) as count, SUM(amount) as total_amount')
                    ->whereBetween('incurred_on', [$start, $end])
                    ->groupBy('category')
                    ->orderByDesc('total_amount')
            )
            ->columns([
                TextColumn::make('category')
                    ->label('Expense Category')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('count')
                    ->label('Number of Expenses')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('total_amount')
                    ->label('Total Amount')
                    ->money('tzs')
                    ->sortable()
                    ->weight('bold')
                    ->color('danger'),
            ])
            ->paginated(false);
    }

    public function getTableRecordKey($record): string
    {
        return (string) $record->category;
    }
}
