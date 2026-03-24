<?php

namespace App\Filament\Resources\StockAdjustments\Pages;

use App\Models\Item;
use App\Models\Store;
use Filament\Schemas\Schema;
use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Forms\Components\Repeater\TableColumn;
use App\Filament\Resources\StockAdjustments\StockAdjustmentResource;

class EditStockAdjustmentt extends EditRecord
{
    protected static string $resource = StockAdjustmentResource::class;


        public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                    Repeater::make('adjustments')
                    ->label('Bulk Adjustments')
                            ->collapsible()
                            ->defaultItems(1)
                            ->minItems(1)
                            ->rules(['array', 'min:1'])

                                ->table([
                                TableColumn::make('Item')
                                ->width('300px'),
                                TableColumn::make('Store')
                                ->width('170px'),
                                TableColumn::make('Qnty on Hand')
                                ->width('90px')
                                ->wrapHeader(),
                                TableColumn::make('New Qnty')
                                ->width('120px'),
                                TableColumn::make('Change Qnty')
                                ->width('150px'),
                                TableColumn::make('Reason'),
                            ])
                            ->schema([
                                Select::make('item_id')
                                    ->label('Item')
                                    ->options(fn () => Item::pluck('name', 'id')->toArray())
                                    ->searchable()
                                    ->required()
                                    // ->preload()
                                    // ->live()
                                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                        if (!$state) return;

                                        $item = Item::find($state);
                                        if (!$item) return;

                                        $storeId = $get('../../store_id'); // parent store

                                        if (!$storeId) {
                                            $set('quantity_on_hand', 0);
                                            $set('quantity_before', 0);
                                            $set('quantity_after', 0);
                                            $set('quantity_change', null);
                                            return;
                                        }

                                        $qty = $item->getQuantityForStore($storeId);

                                        $set('quantity_on_hand', $qty);
                                        $set('quantity_before', $qty);
                                        $set('quantity_after', $qty);
                                        $set('quantity_change', null);
                                    })
                                    ->columnSpan(2),

                            Select::make('store_id')
                                    ->label('Store')
                                    ->searchable()
                                    ->required()
                                    ->options(fn () => Store::pluck('name', 'id')->toArray())
                                    ->default(fn () => Auth::user()?->store_id)
                                    ->live()
                                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                        $itemId = $get('item_id');
                                        if (!$itemId || !$state) {
                                            $set('quantity_on_hand', 0);
                                            $set('quantity_before', 0);
                                            $set('quantity_after', 0);
                                            $set('quantity_change', null);
                                            return;
                                        }

                                        $item = Item::find($itemId);
                                        if (!$item) return;

                                        $qty = $item->getQuantityForStore($state);

                                        $set('quantity_on_hand', $qty);
                                        $set('quantity_before', $qty);
                                        $set('quantity_after', $qty);
                                        $set('quantity_change', null);
                                    }),

                                TextInput::make('quantity_on_hand')
                                    ->label('Qnty on Hand')
                                    ->numeric( )
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->extraInputAttributes(['width' => 30]),

                                TextInput::make('new_quantity')
                                    ->label('New Quantity')
                                    ->numeric()
                                    ->minValue(0) // Add this - prevents negative values
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                        if (blank($state)) {
                                            $set('quantity_change', null);
                                            return;
                                        }

                                        $onHand = (float) $get('quantity_on_hand');
                                        $newQty = (float) $state;
                                        $change = $newQty - $onHand;

                                        $set('quantity_change', $change);
                                        $set('quantity_after', $newQty);
                                        $set('type', $change >= 0 ? 'increase' : 'decrease');
                                    }),

                                TextInput::make('quantity_change')
                                    ->label('Change Quantity')
                                    ->numeric()
                                    ->live(onBlur: true)
                                    ->prefix(fn (Get $get) => ($get('quantity_change') ?? 0) >= 0 ? '+' : '')
                                    ->rules([
                                        function (Get $get) {
                                            return function (string $attribute, $value, \Closure $fail) use ($get) {
                                                if (blank($value)) {
                                                    return;
                                                }

                                                $onHand = (float) $get('quantity_on_hand');
                                                $change = (float) $value;
                                                $newQty = $onHand + $change;

                                                if ($newQty < 0) {
                                                    $fail("Stock cannot go negative (would be {$newQty}).");
                                                }
                                            };
                                        },
                                    ])
                                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                        if (blank($state)) {
                                            $set('new_quantity', null);
                                            return;
                                        }

                                        $onHand = (float) $get('quantity_on_hand');
                                        $change = (float) $state;
                                        $newQty = $onHand + $change;

                                        // Only update derived fields if valid
                                        if ($newQty >= 0) {
                                            $set('new_quantity', $newQty);
                                            $set('quantity_after', $newQty);
                                            $set('type', $change >= 0 ? 'increase' : 'decrease');
                                        }
                                    }),


                                Select::make('reason')
                                    ->label('Reason')
                                    ->options([
                                        'damage' => 'Damage',
                                        'loss' => 'Loss',
                                        'manufacturing_error' => 'Manufacturing Error',
                                        'expiration' => 'Expiration',
                                        'stock_take' => 'Stock Take Adjustment',
                                        'other' => 'Other',
                                    ]),

                                Hidden::make('quantity_before'),
                                Hidden::make('quantity_after'),
                                Hidden::make('type'),
                                Hidden::make('created_by')
                                    ->default(fn () => Auth::id()),



                            ])
                            ->columns(7)
                            ->columnSpanFull()
                            ->addActionLabel('Add Item Adjustment'),

                            ]);




    }

    protected function getHeaderActions(): array
    {
        return [
            // ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
