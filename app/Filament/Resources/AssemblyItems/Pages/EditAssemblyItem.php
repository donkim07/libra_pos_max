<?php

namespace App\Filament\Resources\AssemblyItems\Pages;

use App\Models\Item;
use App\Models\Unit;
use App\Models\Store;
use App\Models\Category;
use Filament\Actions\Action;
use Filament\Schemas\Schema;
use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Illuminate\Support\Facades\Cache;
use Filament\Actions\ForceDeleteAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Utilities\Get;
use App\Filament\Resources\AssemblyItems\AssemblyItemResource;

class EditAssemblyItem extends EditRecord
{
    protected static string $resource = AssemblyItemResource::class;



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
                TextInput::make('quantity')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('cost_price')
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
                Select::make('status')
                    ->searchable()
                    ->options([
                        'in_stock' => 'In Stock',
                        'out_of_stock' => 'Out of Stock',
                        'pre_order' => 'Pre Order',
                    ])
                    ->required(),
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
                FileUpload::make('image')
                    ->image(),
            ])
            ->columns(3)
            ->columnSpanFull(),

            Section::make('Assembly Items')
                ->schema([
                    Repeater::make('items')
                        ->label('Ingredients /  Components')
                        ->relationship('billofMaterial')
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
                            // ->extraInputAttributes(['width' => 20])
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
                            // ->extraInputAttributes(['width' => 100])
                            ->dehydrated(false)
                            ->live(),

                        TextInput::make('total_cost')
                            ->label('Total Cost')
                            ->numeric()
                            ->prefix('$')
                            // ->extraInputAttributes(['width' => 100])
                            ->disabled()
                            ->dehydrated(false),
                    ])

                    ->columns(5)
                    ->columnSpanFull(),
                ])
            ->columnSpanFull(),


            ]);
    }


    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
