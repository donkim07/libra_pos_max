<?php

namespace App\Filament\Widgets;

use App\Models\Item;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Support\Facades\DB;
use Filament\Tables\Columns\TextColumn;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;

class LowStockItemsTable extends TableWidget
{
    protected int | string | array $columnSpan = 'full';

        use HasWidgetShield;

    protected static ?int $sort = 7;

    protected static bool $isLazy = false;

    protected static ?string $heading = 'Low Stock Items (< 5 units total)';

protected function getTableQuery(): \Illuminate\Database\Eloquent\Builder
{
    return Item::query()
        ->where('item_type', 'inventory')
        ->select('items.*')
        ->selectSub(
            DB::table('item_store')
                ->selectRaw('COALESCE(SUM(quantity), 0)')
                ->whereColumn('item_store.item_id', 'items.id'),
            'total_stock'
        )
        ->having('total_stock', '<', 5)
        ->orderBy('total_stock', 'asc');
}
    protected function getTableColumns(): array
    {

        return [
            TextColumn::make('name')
                ->label('Item Name')
                ->searchable(),

            TextColumn::make('total_stock')
                ->label('Total Stock')
                ->numeric()
                ->sortable(),

            TextColumn::make('sku')
                ->label('SKU')
                ->getStateUsing(fn ($record) => $record->sku ?? 'N/A'),

            TextColumn::make('cost_price')
                ->label('Cost Price')
                ->money('TZS', true)
                ->sortable(),


        ];
    }

    protected function getTableHeading(): ?string
    {
        return 'Low Stock Alert';
    }
}
