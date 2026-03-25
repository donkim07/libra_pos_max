<?php

namespace App\Filament\Resources\Manufacturings\Schemas;

use App\Models\Item;
use App\Models\Store;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;

class BulkManufacturingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Bulk Manufacturing Details')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        Select::make('item_id')
                            ->label('Bulk Assembly Item')
                            ->options(
                                Item::where('item_type', 'assembly')
                                    ->pluck('name', 'id')
                            )
                            ->searchable()
                            ->required()
                            ->disabledOn('edit')
                            ->live(debounce: 300)
                            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                $set('ingredients', []);

                                if (!$state) return;
                            }),

                        DatePicker::make('date_manufactured')
                            ->label('Date Manufactured')
                            ->native(false)
                            ->default(now())
                            ->required()
                            ->disabledOn('edit'),

                        Select::make('store_id')
                            ->label('Store')
                            ->searchable()
                            ->options(fn () => Store::pluck('name', 'id')->toArray())
                            ->default(fn () => Auth::user()?->store_id)
                            ->required()
                            ->disabledOn('edit')
                            ->live(),

                        TextInput::make('quantity')
                            ->label('Total Quantity to Manufacture')
                            ->numeric()
                            ->required()
                            ->minValue(0.0001)
                            ->disabledOn('edit')
                            ->live(debounce: 300)
                            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                $itemId = $get('item_id');
                                if (!$itemId || !(float)$state > 0) {
                                    $set('ingredients', []);
                                    $set('remaining_quantity', 0);
                                    $set('initial_remaining_quantity', 0);
                                    return;
                                }

                                $assembly = Item::with('billOfMaterial.items.component')->find($itemId);
                                if (!$assembly?->billOfMaterial?->items->count()) {
                                    $set('ingredients', []);
                                    return;
                                }

                                $batchQty = (float) ($assembly->billOfMaterial->batch_quantity ?? 1);
                                $multiplier = (float)$state / $batchQty;

                                $storeId = $get('store_id');
                                $ingredients = $assembly->billOfMaterial->items
                                    ->map(function ($bomItem) use ($storeId, $multiplier) {
                                        $comp = $bomItem->component;
                                        if (!$comp) return null;

                                        $qtyUsed = (float)$bomItem->quantity * $multiplier;
                                        $available = $storeId ? $comp->getQuantityForStore($storeId) : 0;

                                        return [
                                            'item_id'            => $comp->id,
                                            'name'               => $comp->name,
                                            'available_in_store' => (float) $available,
                                            'quantity'           => $qtyUsed,
                                            'unit_cost'          => (float) ($comp->cost_price ?? 0),
                                            'total_cost'         => round($qtyUsed * ($comp->cost_price ?? 0), 4),
                                        ];
                                    })
                                    ->filter()
                                    ->values()
                                    ->all();

                                $set('ingredients', $ingredients);
                                $set('remaining_quantity', (float)$state);
                                $set('initial_remaining_quantity', (float)$state);
                            }),

                        Section::make('Ingredients / Components (Total Used)')
                            ->schema([
                                Repeater::make('ingredients')
                                    ->label('')
                                    ->schema([
                                        Hidden::make('item_id'),

                                        TextInput::make('name')
                                            ->label('Component')
                                            ->columnSpan(2)
                                            ->disabled(),

                                        TextInput::make('available_in_store')
                                            ->label('Available in Store')
                                            ->numeric()
                                            ->disabled(),

                                        TextInput::make('quantity')
                                            ->label('Quantity Used')
                                            ->numeric()
                                            ->required()
                                            ->minValue(0.0001)
                                            ->live(debounce: '500ms')
                                            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                                $unit = (float) $get('unit_cost') ?: 0;
                                                $set('total_cost', round($state * $unit, 4));
                                            })
                                            ->rules([
                                                function (Get $get) {
                                                    return function (string $attribute, $value, \Closure $fail) use ($get) {
                                                        $available = (float) $get('available_in_store');
                                                        $name = $get('name') ?? 'this component';

                                                        if ($value > $available) {
                                                            $fail("Only {$available} available in store for {$name}.");
                                                        }
                                                    };
                                                },
                                            ]),

                                        TextInput::make('unit_cost')
                                            ->label('Unit Cost')
                                            ->numeric()
                                            ->disabled(),

                                        TextInput::make('total_cost')
                                            ->label('Line Total')
                                            ->numeric()
                                            ->disabled(),
                                    ])
                                    ->columns(6)
                                    ->collapsible()
                                    ->default([])           // ← important: empty default is ok
                                    ->minItems(0)           // ← allow 0 during create if no bom
                                    ->maxItems(100)
                                    ->deletable(false)
                                    ->addable(false)
                                    ->visible(fn (Get $get) => filled($get('item_id')) && filled($get('quantity'))),
                            ])
                            ->visible(fn (Get $get) => $get('operation') === 'create'),

                        Section::make('Historical Ingredients Used')
                            ->schema([
                                Repeater::make('historical_ingredients')
                                    ->label('')
                                    ->schema([
                                        TextInput::make('name')
                                            ->label('Component')
                                            ->columnSpan(2)
                                            ->disabled(),

                                        TextInput::make('quantity')
                                            ->label('Quantity Used')
                                            ->numeric()
                                            ->disabled(),

                                        TextInput::make('unit_cost')
                                            ->label('Unit Cost')
                                            ->numeric()
                                            ->disabled(),

                                        TextInput::make('total_cost')
                                            ->label('Line Total')
                                            ->numeric()
                                            ->disabled(),
                                    ])
                                    ->columns(5)
                                    ->collapsible()
                                    ->disabled(),
                            ])
                            ->visible(fn (Get $get) => $get('operation') === 'edit'),

                        Section::make('Existing Divisions')
                            ->schema([
                                Repeater::make('existing_divisions')
                                    ->label('')
                                    ->schema([
                                        Select::make('target_item_id')
                                            ->label('Target Assembly Item')
                                            ->disabled(),

                                        TextInput::make('paste_per_unit')
                                            ->label('Base Quantity per Unit')
                                            ->numeric()
                                            ->disabled(),

                                        TextInput::make('quantity_produced')
                                            ->label('Quantity Produced')
                                            ->numeric()
                                            ->disabled(),

                                        TextInput::make('total_base_used')
                                            ->label('Total Base Used')
                                            ->numeric()
                                            ->disabled(),
                                    ])
                                    ->columns(4)
                                    ->collapsible()
                                    ->disabled(),
                            ])
                            ->visible(fn (Get $get) => $get('operation') === 'edit'),

                        Repeater::make('new_divisions')
                            ->label('Add New Divisions / Finished Products')
                            ->schema([
                                Select::make('target_item_id')
                                    ->label('Target Assembly Item')
                                    ->options(function (Get $get) {
                                        $bulkId = $get('../item_id');
                                        return Item::where('item_type', 'assembly')
                                            ->where('id', '!=', $bulkId)
                                            ->pluck('name', 'id');
                                    })
                                    ->searchable()
                                    ->required()
                                    ->live(debounce: 300),

                                TextInput::make('paste_per_unit')
                                    ->label('Base Quantity per Unit')
                                    ->numeric()
                                    ->required()
                                    ->minValue(0.0001)
                                    ->live(debounce: '500ms')
                                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                        $produced = (float) $get('quantity_produced') ?: 0;
                                        $set('total_base_used', round($state * $produced, 4));
                                        self::updateRemaining($get, $set);
                                    }),

                                TextInput::make('quantity_produced')
                                    ->label('Quantity to Produce')
                                    ->numeric()
                                    ->required()
                                    ->minValue(0.0001)
                                    ->live(debounce: '500ms')
                                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                        $per = (float) $get('paste_per_unit') ?: 0;
                                        $set('total_base_used', round($state * $per, 4));
                                        self::updateRemaining($get, $set);
                                    }),

                                TextInput::make('total_base_used')
                                    ->label('Total Base Used')
                                    ->numeric()
                                    ->disabled(),
                            ])
                            ->columns(4)
                            ->columnSpanFull()
                            ->collapsible()
                            ->default([])
                            ->minItems(0)
                            ->maxItems(100)
                            ->deletable(true)
                            ->addable(true)
                            ->live()
                            ->afterStateUpdated(fn (Get $get, Set $set) => self::updateRemaining($get, $set)),

                        Hidden::make('initial_remaining_quantity')
                            ->default(0),

                        TextInput::make('remaining_quantity')
                            ->label('Remaining Base Quantity')
                            ->numeric()
                            ->disabled()
                            ->default(0),

                        Toggle::make('is_finished')
                            ->label('Mark as Finished Divisioning')
                            ->live()
                            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                self::updateRemaining($get, $set);
                                if ($state) {
                                    $remaining = (float) $get('remaining_quantity');
                                    $set('waste_quantity', $remaining > 0 ? $remaining : 0);
                                } else {
                                    $set('waste_quantity', 0);
                                }
                            }),

                        TextInput::make('waste_quantity')
                            ->label('Waste / Remainder Quantity')
                            ->numeric()
                            ->disabled()
                            ->visible(fn (Get $get) => $get('is_finished')),

                        Textarea::make('notes')
                            ->label('Notes')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    protected static function updateRemaining(Get $get, Set $set): void
    {
        $sum = collect($get('new_divisions'))->sum(function ($div) {
            return (float) ($div['total_base_used'] ?? 0);
        });

        $current = $get('operation') === 'create'
            ? (float) $get('quantity')
            : (float) $get('initial_remaining_quantity');

        $newRemaining = max(0, $current - $sum);
        $set('remaining_quantity', $newRemaining);

        if ($get('is_finished')) {
            $set('waste_quantity', $newRemaining > 0 ? $newRemaining : 0);
        }
    }
}
