<?php

namespace App\Filament\Resources\Manufacturings\Schemas;

use App\Models\Item;
use App\Models\Store;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;

class ManufacturingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Manufacturing Details')
                ->columns(2)
                ->columnSpanFull()
                ->schema([

                    Select::make('item_id')
                        ->label('Assembly Item')
                        ->options(
                            Item::where('item_type', 'assembly')
                                ->pluck('name', 'id')
                        )
                        ->searchable()
                        ->required()
                        ->live(debounce: 300)
                        ->afterStateUpdated(function ($state, Set $set, $livewire, Get $get) {
                            $set('ingredients', []);

                            if (!$state) return;

                            $useBom = true;

                            if ($livewire instanceof \App\Filament\Resources\Manufacturings\Pages\EditManufacturing && $livewire->record) {
                                if ((int)$state === (int)$livewire->record->item_id) {
                                    $useBom = false; // preserve historical in edit
                                }
                            }

                            if ($useBom) {
                                $assembly = Item::with('billOfMaterial.items.component')->find($state);

                                if (!$assembly?->billOfMaterial?->items->count()) {
                                    return;
                                }

                                $storeId = $get('store_id');
                                $ingredients = $assembly->billOfMaterial->items
                                    ->map(function ($bomItem) use ($storeId) {
                                        $comp = $bomItem->component;
                                        if (!$comp) return null;

                                        $qtyPerUnit = $bomItem->quantity;
                                        $available = $storeId ? $comp->getQuantityForStore($storeId) : 0;

                                        return [
                                            'item_id'     => $comp->id,
                                            'name'        => $comp->name,
                                            'available_in_store' => (float) $available,
                                            'quantity'    => (float) $qtyPerUnit, // qty per 1 unit
                                            'unit_cost'   => (float) $comp->cost_price ?? 0,
                                            'total_cost'  => round($qtyPerUnit * ($comp->cost_price ?? 0), 4),
                                        ];
                                    })
                                    ->filter()
                                    ->values()
                                    ->all();

                                $set('ingredients', $ingredients);
                            }
                        }),

                    DatePicker::make('date_manufactured')
                        ->label('Date Manufactured')
                        ->native(false)
                        ->default(now())
                        ->required(),

                    Select::make('store_id')
                        ->label('Store')
                        ->searchable()
                        ->options(fn () => Store::pluck('name', 'id')->toArray())
                        ->default(fn () => Auth::user()?->store_id)
                        ->required()
                        ->live()
                        ->afterStateUpdated(function (Set $set, Get $get) {
                            // When store changes in create → refresh ingredient availabilities
                            $ingredients = $get('ingredients') ?? [];
                            $storeId = $get('store_id');

                            if (!$storeId) return;

                            $updated = collect($ingredients)->map(function ($ing) use ($storeId) {
                                $item = Item::find($ing['item_id'] ?? 0);
                                $ing['available_in_store'] = $item ? $item->getQuantityForStore($storeId) : 0;
                                return $ing;
                            })->toArray();

                            $set('ingredients', $updated);
                        })
                        ->disabledOn('edit'),

                    Repeater::make('ingredients')
                        ->label('Ingredients / Components')
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
                                ->label('Qty per Unit')
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
                                            $perUnitQty    = (float) $value;
                                            $available     = (float) $get('available_in_store');
                                            $manufactureQty = (float) $get('../../quantity');
                                            $name          = $get('name') ?? 'this component';

                                            if ($manufactureQty <= 0) return;

                                            $totalRequired = $perUnitQty * $manufactureQty;

                                            if ($totalRequired > $available) {
                                                $fail("Need {$totalRequired} total, but only {$available} available in store.");
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
                        ->columnSpanFull()
                        ->collapsible()
                        ->default([])
                        ->minItems(1)
                        ->maxItems(100)
                        ->deletable(false)
                        ->addable(false),

                    TextInput::make('quantity')
                        ->label('Quantity to Manufacture')
                        ->numeric()
                        ->required()
                        ->minValue(1)
                        ->disabledOn('edit'),

                    Textarea::make('notes')
                        ->label('Notes')
                        ->columnSpanFull(),
                ]),
        ]);
    }
}
