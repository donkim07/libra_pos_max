<?php

namespace App\Filament\Widgets;

use App\Models\Item;
use App\Models\Store;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Facades\DB;

class StockPerStoreTable extends TableWidget
{
    use InteractsWithPageFilters;

    protected static bool $isLazy = false;
    
    protected static bool $isDiscovered = false;
    protected int | string | array $columnSpan = 'full';
    protected $listeners = ['filtersUpdated' => '$refresh'];

    protected function getTableHeading(): ?string
    {
        return 'Stock Levels per Store';
    }

    public function table(Table $table): Table
    {
        $storeId = $this->filters['storeId'] ?? null;

        // Use a subquery join approach instead of GROUP BY
        $query = Item::query()
            ->with(['stores' => function ($q) use ($storeId) {
                if ($storeId) {
                    $q->where('store_id', $storeId);
                }
            }])
            ->leftJoinSub(
                DB::table('item_store')
                    ->select('item_id', DB::raw('COALESCE(SUM(quantity), 0) as total_stock'))
                    ->groupBy('item_id'),
                'stock_totals',
                'items.id',
                '=',
                'stock_totals.item_id'
            )
            ->selectRaw('items.*, COALESCE(stock_totals.total_stock, 0) as total_stock');

        if ($storeId) {
            $query->whereHas('stores', fn($q) => $q->where('store_id', $storeId));
        }

        // Get all stores to generate columns
        $stores = $storeId
            ? Store::where('id', $storeId)->get()
            : Store::all();

        // Build columns array
        $columns = [
            TextColumn::make('name')
                ->label('Item')
                ->searchable()
                ->sortable(),
        ];

        // Dynamically add a column for each store
        foreach ($stores as $store) {
            $columns[] = TextColumn::make("store_{$store->id}")
                ->label($store->name)
                ->numeric()
                ->default(0)
                ->state(function (Item $record) use ($store): int {
                    $storeData = $record->stores->firstWhere('id', $store->id);
                    return $storeData?->pivot?->quantity ?? 0;
                })
                ->sortable(false)
                ->color(fn ($state): string => match (true) {
                    $state == 0 => 'danger',
                    $state < 10 => 'warning',
                    default => 'success',
                });
        }

        // Add total column at the end
        $columns[] = TextColumn::make('total_stock')
            ->label('Total Stock')
            ->numeric()
            ->sortable()
            ->weight('bold')
            ->color('primary');

        return $table
            ->query($query)
            ->columns($columns)
            ->filters([
                Tables\Filters\Filter::make('low_stock')
                    ->label('Low Stock (< 10)')
                    ->query(fn (Builder $query) => $query->where('stock_totals.total_stock', '<', 10)),

                Tables\Filters\Filter::make('out_of_stock')
                    ->label('Out of Stock')
                    ->query(fn (Builder $query) => $query->where(function($q) {
                        $q->whereNull('stock_totals.total_stock')
                          ->orWhere('stock_totals.total_stock', '=', 0);
                    })),

                // Stock Range Filter
                Filter::make('stock_range')
                    ->form([
                        Select::make('store')
                            ->label('Store')
                            ->options(
                                Store::pluck('name', 'id')->prepend('All Stores (Total)', 'all')
                            )
                            ->default('all')
                            ->live(),

                        TextInput::make('min_stock')
                            ->label('Minimum Stock')
                            ->numeric()
                            ->placeholder('e.g., 0')
                            ->minValue(0),

                        TextInput::make('max_stock')
                            ->label('Maximum Stock')
                            ->numeric()
                            ->placeholder('e.g., 100')
                            ->minValue(0),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $store = $data['store'] ?? 'all';
                        $min = $data['min_stock'] ?? null;
                        $max = $data['max_stock'] ?? null;

                        // If filtering by total stock across all stores
                        if ($store === 'all') {
                            if ($min !== null && $max !== null) {
                                $query->whereBetween('stock_totals.total_stock', [$min, $max]);
                            } elseif ($min !== null) {
                                $query->where('stock_totals.total_stock', '>=', $min);
                            } elseif ($max !== null) {
                                $query->where('stock_totals.total_stock', '<=', $max);
                            }
                        }
                        // If filtering by specific store
                        else {
                            $query->whereHas('stores', function ($q) use ($store, $min, $max) {
                                $q->where('store_id', $store);

                                if ($min !== null && $max !== null) {
                                    $q->whereBetween('item_store.quantity', [$min, $max]);
                                } elseif ($min !== null) {
                                    $q->where('item_store.quantity', '>=', $min);
                                } elseif ($max !== null) {
                                    $q->where('item_store.quantity', '<=', $max);
                                }
                            });
                        }

                        return $query;
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['store'] ?? null) {
                            $storeName = $data['store'] === 'all'
                                ? 'All Stores (Total)'
                                : Store::find($data['store'])?->name ?? 'Unknown';
                            $indicators['store'] = 'Store: ' . $storeName;
                        }

                        if (($data['min_stock'] ?? null) !== null) {
                            $indicators['min_stock'] = 'Min: ' . $data['min_stock'];
                        }

                        if (($data['max_stock'] ?? null) !== null) {
                            $indicators['max_stock'] = 'Max: ' . $data['max_stock'];
                        }

                        return $indicators;
                    }),
            ])
            ->deferFilters(false)
            ->defaultSort('name', 'asc')
            ->paginated([10, 25, 50, 'all'])
            ->striped();
    }
}
