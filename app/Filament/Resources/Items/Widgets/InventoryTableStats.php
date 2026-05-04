<?php

namespace App\Filament\Resources\Items\Widgets;

use App\Models\Item;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class InventoryTableStats extends StatsOverviewWidget
{
    // protected ?string $heading = 'Inventory Snapshot';

    protected static bool $isLazy = false;

    protected function getStats(): array
    {
        $inventory = Item::query()->where('item_type', 'inventory');

        $allStock = (float) DB::table('item_store')->sum('quantity');

        $lowStockCount = Item::query()
            ->where('item_type', 'inventory')
            ->select('items.id')
            ->selectSub(
                fn ($q) => $q->from('item_store')->selectRaw('COALESCE(SUM(item_store.quantity), 0)')->whereColumn('item_store.item_id', 'items.id'),
                'total_stock'
            )
            ->having('total_stock', '<', 5)
            ->count();

        return [
            Stat::make('Inventory SKUs', number_format((int) $inventory->count()))
                ->description('Total inventory items')
                ->descriptionIcon('heroicon-m-cube')
                ->color('info'),
            Stat::make('Total On Hand', number_format($allStock, 2))
                ->description('Across all stores')
                ->descriptionIcon('heroicon-m-archive-box')
                ->color('success'),
            Stat::make('Low Stock (< 5)', number_format($lowStockCount))
                ->description('Items near stockout')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('warning'),
        ];
    }
}

