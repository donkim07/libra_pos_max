<?php

namespace App\Filament\Resources\Manufacturings\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Placeholder;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Utilities\Get;

class ManufacturingInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
             Flex::make([
                Section::make()
                    ->schema([
                Select::make('item_id')
                    ->relationship('item', 'name'),
                TextEntry::make('quantity')
                    ->badge()
                    ->color('primary')
                    ->numeric(),

                Select::make('store_id')
                    ->relationship('store', 'name'),
                TextEntry::make('total_cost')
                    ->badge()
                    ->money('TSH'),
            ])
            ->columns(2)
            // ->columnSpanFull(),
                ])
            ->columns(2)
            ->columnSpanFull(),


             Section::make('Manufacturing Details')
                ->icon('heroicon-o-cube')
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

                                TextEntry::make('quantity')
                                    ->label('Quantity Used')
                                    ->numeric()
                                    ->disabled(),

                                TextEntry::make('unit_cost')
                                    ->label('Unit Cost')
                                    ->numeric()
                                    ->disabled(),

                                TextEntry::make('total_cost')
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


                Section::make('Audit Trail')
                ->icon('heroicon-o-clock')
                ->description('Record creation and modification history')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextEntry::make('created_at')
                                ->label('Created')
                                ->dateTime('M j, Y - g:i A')
                                ->icon('heroicon-o-plus-circle')
                                ->default('N/A'),
                            TextEntry::make('updated_at')
                                ->label('Last Updated')
                                ->dateTime('M j, Y - g:i A')
                                ->icon('heroicon-o-arrow-path')
                                ->default('N/A'),
                            // TextEntry::make('deleted_at')
                            //     ->label('Deleted')
                            //     ->dateTime('M j, Y - g:i A')
                            //     ->icon('heroicon-o-trash')
                            //     ->color('danger')
                            //     ->visible(fn (Item $record): bool => $record->trashed()),
                        ]),
                    Grid::make(2)
                        ->schema([
                            TextEntry::make('creator.name')
                                ->label('Created By')
                                ->icon('heroicon-o-user')
                                ->badge()
                                ->default('System'),
                            // TextEntry::make('updator.name')
                            //     ->label('Updated By')
                            //     ->icon('heroicon-o-user')
                            //     ->badge()
                            //     ->default('N/A'),

                        ]),
                ])
                ->collapsed()
                ->collapsible(),
        ]);

    }
}
