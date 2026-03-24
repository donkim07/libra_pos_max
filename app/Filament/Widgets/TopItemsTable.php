<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use App\Models\SalesItem;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Filament\Tables\Columns\TextColumn;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class TopItemsTable extends TableWidget
{
    use InteractsWithPageFilters;

    protected static bool $isDiscovered = false;
    protected static bool $isLazy = false;
    protected int | string | array $columnSpan = 'full';
    protected $listeners = ['filtersUpdated' => '$refresh'];

    protected function getTableHeading(): ?string
    {
        return 'Top Selling Items';
    }

    // Add this method to provide a custom record key
    public function getTableRecordKey($record): string
    {
        return (string) $record->item_id;
    }

    public function table(Table $table): Table
    {
        $startDate = $this->filters['startDate'] ?? null;
        $endDate   = $this->filters['endDate']   ?? null;
        $storeId   = $this->filters['storeId']   ?? null;

        return $table
            ->query(
                SalesItem::query()
                    ->selectRaw('item_id, SUM(quantity) as total_qty, SUM(total) as total_revenue')
                    ->with(['item' => fn($q) => $q->select('id', 'name')])
                    ->when($startDate, fn($q) => $q->whereHas('sale', fn($sq) => $sq->whereDate('created_at', '>=', $startDate)))
                    ->when($endDate,   fn($q) => $q->whereHas('sale', fn($sq) => $sq->whereDate('created_at', '<=', $endDate)))
                    ->when($storeId,   fn($q) => $q->where('store_id', $storeId))
                    ->groupBy('item_id')
                    ->orderByDesc('total_revenue')
                    ->limit(15)
            )
            ->columns([
                TextColumn::make('item.name')
                    ->label('Item')
                    ->searchable(),
                TextColumn::make('total_qty')
                    ->label('Quantity Sold')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('total_revenue')
                    ->label('Revenue')
                    ->money('tzs')
                    ->sortable(),
            ])
            ->defaultSort('total_revenue', 'desc')
            ->striped();
            // ->paginated(false); // Optional: disable pagination for top items
    }
}
