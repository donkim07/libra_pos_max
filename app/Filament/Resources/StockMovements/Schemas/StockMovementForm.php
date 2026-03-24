<?php

namespace App\Filament\Resources\StockMovements\Schemas;

use App\Models\Item;
use App\Models\Store;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Forms\Components\Repeater\TableColumn;

class StockMovementForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Repeater::make('movements')
                    ->label('Bulk Stock Transfers')
                    ->collapsible()
                    ->defaultItems(1)
                    ->minItems(1)
                    ->rules(['array', 'min:1'])

                    ->table([
                        TableColumn::make('Item')
                            ->width('300px'),
                        TableColumn::make('Source Store')
                            ->width('170px'),
                        TableColumn::make('Qnty on Source')
                            ->width('120px')
                            ->wrapHeader(),
                        TableColumn::make('Destination Store')
                            ->width('170px'),
                        TableColumn::make('Quantity Sent')
                            ->width('140px'),
                    ])
                    ->schema([
                        Select::make('item_id')
                            ->label('Item')
                            ->options(fn () => Item::pluck('name', 'id')->toArray())
                            ->searchable()
                            ->required()
                            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                if (!$state) {
                                    $set('quantity_on_source', 0);
                                    $set('quantity_to_send', null);
                                    return;
                                }

                                $item = Item::find($state);
                                if (!$item) return;

                                $sourceStoreId = $get('source_store_id');
                                if (!$sourceStoreId) {
                                    $set('quantity_on_source', 0);
                                    return;
                                }

                                $qty = $item->getQuantityForStore($sourceStoreId);
                                $set('quantity_on_source', $qty);
                            })
                            ->columnSpan(2),

                        Select::make('source_store_id')
                            ->label('Source Store')
                            ->searchable()
                            ->required()
                            ->options(fn () => Store::pluck('name', 'id')->toArray())
                            ->default(fn () => Auth::user()?->store_id)
                            ->live()
                            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                $itemId = $get('item_id');
                                if (!$itemId || !$state) {
                                    $set('quantity_on_source', 0);
                                    $set('quantity_to_send', null);
                                    return;
                                }

                                $item = Item::find($itemId);
                                if (!$item) return;

                                $qty = $item->getQuantityForStore($state);
                                $set('quantity_on_source', $qty);
                            }),

                        TextInput::make('quantity_on_source')
                            ->label('Qnty on Source')
                            ->numeric()
                            ->disabled()
                            ->dehydrated(false),

                        Select::make('destination_store_id')
                            ->label('Destination Store')
                            ->searchable()
                            ->required()
                            ->options(fn () => Store::pluck('name', 'id')->toArray())
                            ->rules([
                                function (Get $get) {
                                    return function (string $attribute, $value, \Closure $fail) use ($get) {
                                        $source = $get('source_store_id');
                                        if ($value && $source && $value == $source) {
                                            $fail('Destination store cannot be the same as source store.');
                                        }
                                    };
                                },
                            ]),

                        TextInput::make('quantity_to_send')
                            ->label('Quantity Sent')
                            ->numeric()
                            ->minValue(0.0001) // prevent zero/negative transfers
                            ->live(onBlur: true)
                            ->rules([
                                function (Get $get) {
                                    return function (string $attribute, $value, \Closure $fail) use ($get) {
                                        if (blank($value)) return;

                                        $sourceQty = (float) $get('quantity_on_source');
                                        $sendQty   = (float) $value;

                                        if ($sendQty > $sourceQty) {
                                            $fail("Cannot send more than available ({$sourceQty}).");
                                        }
                                    };
                                },
                            ])
                            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                // Optional: could add visual feedback, but validation above is main protection
                            }),

                        Hidden::make('created_by')
                            ->default(fn () => Auth::id()),
                    ])
                    ->columns(5)
                    ->columnSpanFull()
                    ->addActionLabel('Add Transfer Item'),
            ]);
    }
}
