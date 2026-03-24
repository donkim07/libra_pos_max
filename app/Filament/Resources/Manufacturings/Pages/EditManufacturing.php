<?php

namespace App\Filament\Resources\Manufacturings\Pages;

use App\Models\Item;
use Filament\Schemas\Schema;
use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Form;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Components\Utilities\Get;
use App\Filament\Resources\Manufacturings\ManufacturingResource;

class EditManufacturing extends EditRecord
{
    protected static string $resource = ManufacturingResource::class;

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Manufacturing Details')
                    ->schema([
                // Your other fields (item_id, quantity, store_id, etc.)
                // Probably disabled or read-only
                Select::make('item_id')
                    ->label('Assembly Item')
                    ->relationship('item', 'name')
                    ->disabled(),
                TextInput::make('quantity')
                    ->label('Quantity')
                    ->numeric()
                    ->disabled(),
                Select::make('store_id')
                    ->label('Store')
                    ->relationship('store', 'name')
                    ->disabled(),
                TextInput::make('total_cost')
                    ->label('Total Cost')
                    ->prefix('TSH')
                    ->disabled(),
                // TextInput::make('notes')
                //     ->label('Notes')
                //     ->disabled(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),




                Section::make('Used Ingredients')
                    ->schema([
                        Repeater::make('ingredients')
                            ->label(false)
                            ->relationship('manufacturingItems')   // ← magic line!
                            ->schema([
                                Select::make('item_id')
                                    ->label('Item Name')
                                    ->relationship('item', 'name')
                                    //  ->options(function () {
                                    //      return Item::where('assembly', true)
                                    //          ->orderBy('name')
                                    //          ->pluck('name', 'id');
                                    //  })
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
                            ->columns(4)
                            ->collapsible()
                            // ->collapsed()
                            ->deletable(false)
                            ->addable(false)
                            ->reorderable(false)
                            ->extraItemActions([])
                            ->itemLabel(fn (array $state): ?string => $state['item']['name'] ?? null),

                        // Summary total
                        Placeholder::make('total_cost_summary')
                            ->label('Total Cost of Ingredients')
                            ->content(function (Get $get) {
                                $items = $get('ingredients') ?? [];
                                $sum = collect($items)->sum('total_cost');
                                return number_format($sum, 2);
                            })
                            ->extraAttributes(['class' => 'text-lg font-bold text-primary-600']),
                    ])
                    ->columnSpanFull(),
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

