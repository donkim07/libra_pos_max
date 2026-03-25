<?php

namespace App\Filament\Resources\Manufacturings\Schemas;

use App\Models\Item;
use App\Models\Store;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Placeholder;
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
                        Hidden::make('operation')
                            ->default('create')
                            ->dehydrated(false),

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

                                if (!$state) {
                                    return;
                                }

                                self::fillIngredientsForBulkItem($get, $set);
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
                            ->live()
                            ->afterStateUpdated(function (Set $set, Get $get) {
                                if (!self::isCreateOperation($get)) {
                                    return;
                                }

                                self::fillIngredientsForBulkItem($get, $set);
                            }),

                        TextInput::make('quantity')
                            ->label('Total Quantity to Manufacture')
                            ->numeric()
                            ->required()
                            ->minValue(0.0001)
                            ->disabledOn('edit')
                            ->live(debounce: 300)
                            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                self::fillIngredientsForBulkItem($get, $set);
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
                            ->columnSpanFull()
                            ->visible(fn (Get $get) => self::isCreateOperation($get)),

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

                            ->columnSpanFull()
                            ->collapsible()
                            ->collapsed()
                            ->visible(fn (Get $get) => self::isEditOperation($get)),

                        Section::make('Existing Divisions')
                            ->schema([
                                Repeater::make('existing_divisions')
                                    ->label('')
                                    ->schema([
                                        // TextInput::make('target.name')
                                        //     ->label('Target Assembly Item')
                                        //     ->disabled(),

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

                                        Section::make('Bill of materials (saved run)')
                                            ->description('Strikethrough: components already counted in the bulk BOM; they were not deducted again for this division.')
                                            ->schema([
                                                Placeholder::make('existing_division_bom_preview')
                                                    ->label('')
                                                    ->content(fn (Get $get) => self::divisionBomPreview($get)),
                                            ])
                                            ->collapsible()
                                            ->collapsed()
                                            ->columnSpanFull(),
                                    ])
                                    ->columns(4)
                                    ->columnSpanFull()
                                    ->collapsible()
                                    ->collapsed()
                                    ->itemLabel(function (array $state): ?string {
                                        $id = $state['target_item_id'] ?? null;
                                        if (!$id) {
                                            return __('Division');
                                        }

                                        return Item::find($id)?->name ?? __('Division');
                                    })
                                    ->disabled(),
                            ])
                            ->columnSpanFull()
                            ->collapsible()
                            ->collapsed()
                            ->visible(fn (Get $get) => self::isEditOperation($get)),

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
                                    ->columnSpan(2)
                                    ->live(debounce: 300),

                                TextInput::make('paste_per_unit')
                                    ->label('Base Quantity per Unit')
                                    ->numeric()
                                    ->required()
                                    ->minValue(0.0001)
                                    ->live(onBlur: true)
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
                                    ->live(debounce: 200)
                                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                        $per = (float) $get('paste_per_unit') ?: 0;
                                        $set('total_base_used', round($state * $per, 4));
                                        self::updateRemaining($get, $set);
                                    }),

                                TextInput::make('total_base_used')
                                    ->label('Total Base Used')
                                    ->numeric()
                                    ->disabled(),

                                Section::make('Bill of materials (preview)')
                                    ->description('Strikethrough lines are already supplied by the bulk batch BOM and are not deducted again when this division is saved.')
                                    ->schema([
                                        Placeholder::make('division_bom_preview')
                                            ->label('')
                                            ->content(fn (Get $get) => self::divisionBomPreview($get)),
                                    ])
                                    ->collapsible()
                                    ->columnSpanFull(),
                            ])
                            ->columns(5)
                            ->columnSpanFull()
                            ->collapsible()
                            ->collapsed()
                            ->itemLabel(function (array $state): ?string {
                                $id = $state['target_item_id'] ?? null;
                                if (!$id) {
                                    return __('New division');
                                }

                                return Item::find($id)?->name ?? __('Division');
                            })
                            ->default([])
                            ->minItems(0)
                            ->maxItems(100)
                            ->deletable(true)
                            ->addable(true)
                            ->live()
                            ->afterStateUpdated(fn (Get $get, Set $set) => self::updateRemaining($get, $set))
                            ->rules([
                                function (Get $get) {
                                    return function (string $attribute, $value, \Closure $fail) use ($get) {
                                        $sum = self::sumDivisionBaseUsedFromRows(is_array($value) ? $value : []);
                                        $max = self::divisionCapacityMax($get);

                                        if ($sum <= 0.0001) {
                                            return;
                                        }

                                        if ($max <= 0) {
                                            $fail(__('Set the total bulk quantity (or ensure this batch has remaining base quantity) before adding divisions.'));

                                            return;
                                        }

                                        if ($sum > $max + 0.0001) {
                                            $fail(__('Total base used (:used) cannot exceed available bulk quantity (:avail). Remove a division or lower quantities.', [
                                                'used' => number_format($sum, 4),
                                                'avail' => number_format($max, 4),
                                            ]));
                                        }
                                    };
                                },
                            ]),

                        Placeholder::make('new_divisions_capacity_alert')
                            ->label('')
                            ->columnSpanFull()
                            ->visible(fn (Get $get) => self::divisionAllocationsAreInvalid($get))
                            ->content(fn (Get $get) => self::divisionCapacityAlertContent($get)),

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

    protected static function fillIngredientsForBulkItem(Get $get, Set $set): void
    {
        $itemId = $get('item_id');
        $qtyRaw = $get('quantity');

        if (!$itemId || !((float) $qtyRaw > 0)) {
            $set('ingredients', []);
            $set('remaining_quantity', 0);
            $set('initial_remaining_quantity', 0);

            return;
        }

        $qty = (float) $qtyRaw;

        $assembly = Item::with('billOfMaterial.items.component')->find($itemId);
        if (!$assembly?->billOfMaterial?->items->count()) {
            $set('ingredients', []);
            $set('remaining_quantity', $qty);
            $set('initial_remaining_quantity', $qty);

            return;
        }

        $batchQty = (float) ($assembly->billOfMaterial->batch_quantity ?? 1);
        $multiplier = $qty / $batchQty;

        $storeId = $get('store_id');
        $ingredients = $assembly->billOfMaterial->items
            ->map(function ($bomItem) use ($storeId, $multiplier) {
                $comp = $bomItem->component;
                if (!$comp) {
                    return null;
                }

                $qtyUsed = (float) $bomItem->quantity * $multiplier;
                $available = $storeId ? $comp->getQuantityForStore($storeId) : 0;

                return [
                    'item_id' => $comp->id,
                    'name' => $comp->name,
                    'available_in_store' => (float) $available,
                    'quantity' => $qtyUsed,
                    'unit_cost' => (float) ($comp->cost_price ?? 0),
                    'total_cost' => round($qtyUsed * ($comp->cost_price ?? 0), 4),
                ];
            })
            ->filter()
            ->values()
            ->all();

        $set('ingredients', $ingredients);
        $set('remaining_quantity', $qty);
        $set('initial_remaining_quantity', $qty);
    }

    protected static function updateRemaining(Get $get, Set $set): void
    {
        $sum = self::sumDivisionBaseUsedFromForm($get);

        $current = self::isCreateOperation($get)
            ? (float) $get('quantity')
            : (float) $get('initial_remaining_quantity');

        $newRemaining = max(0, $current - $sum);
        $set('remaining_quantity', $newRemaining);

        if ($get('is_finished')) {
            $set('waste_quantity', $newRemaining > 0 ? $newRemaining : 0);
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     */
    protected static function sumDivisionBaseUsedFromRows(array $rows): float
    {
        return (float) collect($rows)->sum(function ($div) {
            if (! is_array($div)) {
                return 0;
            }

            return ((float) ($div['paste_per_unit'] ?? 0)) * ((float) ($div['quantity_produced'] ?? 0));
        });
    }

    protected static function sumDivisionBaseUsedFromForm(Get $get): float
    {
        $raw = $get('new_divisions');

        return self::sumDivisionBaseUsedFromRows(is_array($raw) ? $raw : []);
    }

    protected static function divisionCapacityMax(Get $get): float
    {
        return self::isCreateOperation($get)
            ? (float) $get('quantity')
            : (float) $get('initial_remaining_quantity');
    }

    protected static function divisionAllocationsAreInvalid(Get $get): bool
    {
        $sum = self::sumDivisionBaseUsedFromForm($get);
        if ($sum <= 0.0001) {
            return false;
        }

        $max = self::divisionCapacityMax($get);
        if ($max <= 0) {
            return true;
        }

        return $sum > $max + 0.0001;
    }

    protected static function divisionCapacityAlertContent(Get $get): HtmlString
    {
        $sum = self::sumDivisionBaseUsedFromForm($get);
        $max = self::divisionCapacityMax($get);

        if ($max <= 0) {
            return new HtmlString(
                '<p class="text-sm font-medium text-danger-600 dark:text-danger-400">'
                . e(__('You have divisions allocated, but no available bulk quantity yet. Enter the manufactured quantity on create, or check remaining base on edit.'))
                . '</p>'
            );
        }

        $over = $sum - $max;

        return new HtmlString(
            '<p class="text-sm font-medium text-danger-600 dark:text-danger-400">'
            . e(__('Total base used is :used; available is only :avail (over by :over). You cannot save until this is fixed.', [
                'used' => number_format($sum, 4),
                'avail' => number_format($max, 4),
                'over' => number_format(max(0, $over), 4),
            ]))
            . '</p>'
        );
    }

    protected static function isCreateOperation(Get $get): bool
    {
        return ($get('operation') ?? 'create') === 'create';
    }

    protected static function isEditOperation(Get $get): bool
    {
        return ($get('operation') ?? '') === 'edit';
    }

    protected static function divisionBomPreview(Get $get): HtmlString
    {
        $targetId = $get('target_item_id');
        $bulkItemId = $get('../../item_id');
        $produced = (float) ($get('quantity_produced') ?: 0);

        if (!$targetId) {
            return new HtmlString(
                '<p class="text-sm text-gray-500 dark:text-gray-400">Select a target assembly to preview components.</p>'
            );
        }

        $target = Item::with('billOfMaterial.items.component')->find($targetId);
        if (!$target?->billOfMaterial) {
            return new HtmlString(
                '<p class="text-sm text-danger-600 dark:text-danger-400">This assembly has no bill of materials.</p>'
            );
        }

        $bulkComponentIds = [];
        if ($bulkItemId) {
            $bulkAssembly = Item::with('billOfMaterial.items')->find($bulkItemId);
            $bulkComponentIds = $bulkAssembly?->billOfMaterial?->items->pluck('item_id')->unique()->all() ?? [];
        }

        $batchQty = (float) ($target->billOfMaterial->batch_quantity ?? 1);
        $multiplier = $batchQty > 0 ? $produced / $batchQty : 0;

        $lines = [];
        foreach ($target->billOfMaterial->items as $bomItem) {
            $comp = $bomItem->component;
            if (!$comp) {
                continue;
            }

            $fromBulk = in_array((int) $bomItem->item_id, array_map('intval', $bulkComponentIds), true);
            $qtyScaled = (float) $bomItem->quantity * $multiplier;
            $perBatch = e((string) $bomItem->quantity);
            $name = e($comp->name);
            $qtyFormatted = e(number_format($qtyScaled, 4));

            $row = "<span class=\"font-medium\">{$name}</span>"
                . " — <span class=\"tabular-nums\">{$qtyFormatted}</span>"
                . " <span class=\"text-gray-500 dark:text-gray-400\">({$perBatch} " . e(__('per batch')) . ')</span>';

            if ($fromBulk) {
                $lines[] = '<li class="line-through opacity-70">' . $row .
                    ' <span class="text-xs text-gray-500">' . e(__('Already in bulk BOM')) . '</span></li>';
            } else {
                $lines[] = '<li>' . $row . '</li>';
            }
        }

        if ($lines === []) {
            return new HtmlString('<p class="text-sm text-gray-500 dark:text-gray-400">' . e(__('No BOM lines.')) . '</p>');
        }

        return new HtmlString(
            '<ul class="list-disc list-inside text-sm space-y-1">' . implode('', $lines) . '</ul>'
        );
    }
}
