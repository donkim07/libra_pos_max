<?php

namespace App\Filament\Resources\AssemblyItems\RelationManagers;

use App\Models\Item;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Form;
use Illuminate\Support\Facades\Cache;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Forms\Components\Repeater\TableColumn;
use Filament\Resources\RelationManagers\RelationManager;

class BillOfMaterialRelationManager extends RelationManager
{
    protected static string $relationship = 'billOfMaterial';

    protected static ?string $title = 'Bill of Materials';

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

    public function form(Schema $schema): Schema
    {
        $items = self::getItemsCache();

        return $schema
            ->components([
                Repeater::make('items')

                    ->relationship()
                    ->label('Components')
                    ->schema([
                        Select::make('item_id')
                            ->label('Item')
                            ->options($items->pluck('name', 'id'))
                            ->searchable()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set, Get $get) use ($items) {
                                if ($state && $items->has($state)) {
                                    $item = $items->get($state);
                                    $set('unit_cost', $item->cost_price);
                                    $quantity = $get('quantity');
                                    if ($quantity) {
                                        $set('total_cost', $item->cost_price * $quantity);
                                    }
                                }
                            })->columnspan(2)
                            ->afterStateHydrated(function ($state, callable $set) use ($items) {
                                if ($state && $items->has($state)) {
                                    $item = $items->get($state);
                                    $set('unit_cost', $item->cost_price);
                                }
                            }),

                        TextInput::make('quantity')
                            ->numeric()
                            ->required()
                            ->live()
                            ->extraInputAttributes(['width' => 20])
                            ->afterStateUpdated(function ($state, callable $set, Get $get) {
                                $unitCost = $get('unit_cost');
                                if ($unitCost && $state) {
                                    $set('total_cost', $unitCost * $state);
                                }
                            }),

                        TextInput::make('unit_cost')
                            ->label('Unit Cost')
                            ->numeric()
                            ->prefix('$')
                            ->disabled()
                            ->extraInputAttributes(['width' => 100])
                            ->dehydrated(false)
                            ->live(),

                        TextInput::make('total_cost')
                            ->label('Total Cost')
                            ->numeric()
                            ->prefix('$')
                            ->extraInputAttributes(['width' => 100])
                            ->disabled()
                            ->dehydrated(false),
                    ])
                    ->columns(5)
                    ->columnSpanFull()
                    ->required()
                    ->live(),

            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['items.component']))
            ->columns([
                TextColumn::make('components_summary')
                    ->label('Components')
                    ->state(fn ($record) =>
                        $record->items
                            ->map(fn ($item) =>
                                "{$item->component->name} ({$item->quantity})"
                            )
                            ->join(', ')
                    )
                    ->wrap(),

                TextColumn::make('items.quantity')
                    ->label('Quantity')
                    ->listWithLineBreaks()
                    ->bulleted()
                    ->limitList(5)
                    ->expandableLimitedList()
                    ->formatStateUsing(fn ($state) => number_format($state, 2)),

                TextColumn::make('items.unit_cost')
                    ->label('Unit Cost')
                    ->listWithLineBreaks()
                    ->bulleted()
                    ->limitList(5)
                    ->expandableLimitedList()
                    ->money('TSH'),

                TextColumn::make('items.total_cost')
                    ->label('Line Total')
                    ->listWithLineBreaks()
                    ->bulleted()
                    ->limitList(5)
                    ->expandableLimitedList()
                    ->money('TSH')
                    ->summarize(Sum::make()->label('Total')),

                // TextColumn::make('total_cost')
                //     ->label('Grand Total')
                //     ->money('USD')
                //     ->weight('bold')
                //     ->color('success')
                //     // ->size(TextColumn\TextColumnSize::Large)
                //     ->summarize(Sum::make()->label('Total')->color('success')),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
            // ->contentFooter(function ($livewire) {
            //     $record = $livewire->getOwnerRecord()->billOfMaterial;

            //     if (!$record) {
            //         return null;
            //     }

            //     return view('filament.components.bill-of-material-footer', [
            //         'totalCost' => $record->total_cost,
            //     ]);
            // });
    }


}
