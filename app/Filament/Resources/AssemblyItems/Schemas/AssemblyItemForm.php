<?php

namespace App\Filament\Resources\AssemblyItems\Schemas;

use App\Models\Item;
use App\Models\Unit;
use App\Models\Store;
use App\Models\Category;
use Filament\Actions\Action;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Illuminate\Support\Facades\Cache;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;

class AssemblyItemForm
{
    // Cache items for better performance
    protected static function getItemsCache(): \Illuminate\Support\Collection
    {
        return Cache::remember('active_items_with_prices', 3600, function () {
            return Item::query()
                ->where('is_active', true)
                ->select('id', 'name', 'cost_price')
                ->get()
                ->keyBy('id');
        });
    }

    public static function configure(Schema $schema): Schema
    {
        $items = self::getItemsCache();

        return $schema
            ->components([
                Section::make('Basic Information')
                    ->columns(2)
                    ->schema([
                TextInput::make('name')
                    ->required(),
                    Textarea::make('description')
                    ->columns(2),
                    TextInput::make('sku')
                        ->label('SKU')
                        ->unique()
                        ->belowContent([
                            Action::make('generate')
                                ->icon(Heroicon::Sparkles)
                                ->action(function (TextInput $component) {
                                    $component->state('SKU-' . strtoupper(uniqid()));
                                }),
                        ]),
                Select::make('category_id')
                    ->label('Category')
                    ->searchable()
                    ->required()
                    ->options(fn () => Category::pluck('name', 'id')->toArray()),
                Select::make('unit_id')
                    ->label('Unit')
                    ->searchable()
                    ->options(fn () => Unit::pluck('name', 'id')->toArray()),
                // TextInput::make('quantity')
                //     ->required()
                //     ->numeric()
                //     ->default(0),
                TextInput::make('other_cost')
                    ->required()
                    ->numeric()
                    ->prefix('TSH'),
                TextInput::make('selling_price')
                    ->required()
                    ->numeric()
                    ->prefix('TSH'),
                // TextInput::make('discount') // Uncomment if you want it back
                //     ->numeric()
                //     ->default(0),
                Hidden::make('status')
                    ->default('in_stock'),
                    // ->options([
                    //     'in_stock' => 'In Stock',
                    //     'out_of_stock' => 'Out of Stock',
                    //     'pre_order' => 'Pre Order',
                    // ])
                    // ->required(),
                Toggle::make('is_active')
                    ->required()
                    ->default(true),
                Select::make('store_id')
                    ->label('Store')
                    ->searchable()
                    ->options(fn () => Store::pluck('name', 'id')->toArray())
                    ->default(fn () => Auth::user()?->store_id)
                    ->required(),
                Hidden::make('item_type')
                        ->default('assembly'),
                Hidden::make('created_by')
                        ->default(fn () => Auth::id()),
                Hidden::make('updated_by')
                        ->default(fn () => Auth::id()),
                FileUpload::make('image')
                    ->image(),
            ])
            ->columns(3)
            ->columnSpanFull(),

            Section::make('Bill of Materials')
    ->relationship('billOfMaterial')  // ← Correct name: matches Item::billOfMaterial()
    ->schema([
        Hidden::make('created_by')
                        ->default(fn () => Auth::id()),
                Hidden::make('updated_by')
                        ->default(fn () => Auth::id()),

        Repeater::make('items')  // ← No need for ->relationship('items') — implicit from parent
            ->label('Components')
            ->collapsible()
            ->live(debounce: 500)
            ->relationship('items')
            ->afterStateUpdated(function (Get $get, Set $set) {
        $items = $get('items') ?? [];
        $total = collect($items)->sum(fn ($i) => (float) ($i['total_cost'] ?? 0));
        $set('total_cost', number_format($total, 2)); // updates the disabled field live
    })
            ->schema([
                Hidden::make('created_by')
                        ->default(fn () => Auth::id()),
                Hidden::make('updated_by')
                        ->default(fn () => Auth::id()),
                Select::make('item_id')
                    ->label('Item')
                    ->options(self::getItemsCache()->pluck('name', 'id'))
                    ->searchable()
                    ->required()
                    ->live()
                    ->afterStateUpdated(function ($state, callable $set, Get $get) {
                        $items = self::getItemsCache();
                        if ($state && $items->has($state)) {
                            $item = $items->get($state);
                            $set('unit_cost', $item->cost_price);
                            $quantity = $get('quantity');
                            if ($quantity) {
                                $set('total_cost', $item->cost_price * $quantity);
                            }
                        }
                    })
                    ->columnSpan(2),

                TextInput::make('quantity')
                    ->numeric()
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(function ($state, callable $set, Get $get) {
                        $unitCost = $get('unit_cost');
                        if ($unitCost && $state) {
                            $set('total_cost', $unitCost * $state);
                        }
                    }),

                TextInput::make('unit_cost')
                    ->label('Unit Cost')
                    ->numeric()
                    ->prefix('TSH')  // ← Changed to TSH for consistency
                    ->disabled()
                    ->dehydrated(false)
                    ->live(onBlur: true),

                TextInput::make('total_cost')
                    ->label('Total Cost')
                    ->numeric()
                    ->prefix('TSH')  // ← Changed to TSH
                    ->disabled()
                    ->dehydrated(),
            ])
            ->columns(5)
            ->columnSpanFull(),

            TextInput::make('batch_quantity')
            ->label('Batch Quantity (Yield)')
            ->numeric()
            ->default(1)
            ->required()
            ->hidden()
            ->dehydrated()
            ->minValue(1),

                    // Optional: Show calculated total cost (disabled, live updates via model boot or custom JS if needed)
        TextInput::make('total_cost')
            ->label('Total BOM Cost')
            ->prefix('TSH')
            ->disabled()
            ->dehydrated(false) // don't save this — it's calculated
            ->formatStateUsing(fn ($state) => number_format($state ?? 0, 2)),
            ])
            ->columnSpanFull(),

            ]);

    }


    protected static function recalculateAssemblyTotal(array $items, Set $set, Get $get): void
    {
        $total = collect($items)->sum(fn ($item) => (float) ($item['total_cost'] ?? 0));
        $formattedTotal = number_format($total, 2, '.', '');

        $set('total_cost', $formattedTotal);
    }
}
