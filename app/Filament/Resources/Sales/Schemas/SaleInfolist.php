<?php

namespace App\Filament\Resources\Sales\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Placeholder;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\RepeatableEntry\TableColumn;
// use Filament\Infolists\Components\RepeatableEntry;

class SaleInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Flex::make([
                    Section::make()
                        ->schema([
                            TextEntry::make('customer.name'),
                            TextEntry::make('store.name')
                                ->label('Store name')
                                ->color('primary'),
                            TextEntry::make('total')
                                ->money('TSH')
                                ->numeric(),
                            TextEntry::make('paid_amount')
                                ->numeric(),
                            TextEntry::make('discount')
                                ->placeholder('0')
                                ->numeric(),
                            TextEntry::make('status')
                                ->badge(),
                            TextEntry::make('account.name')
                                ->label('Account Name'),
                        ])
                        ->columns(4)
                ])
                ->columnSpanFull(),

                Section::make('Sales Details')
                    ->icon('heroicon-o-cube')
                    ->schema([
                        // ── This is the correct way ──
                        RepeatableEntry::make('saleItems')

                            ->table([
                            TableColumn::make('Item Name'),
                            TableColumn::make('Quantity'),
                            TableColumn::make('Unit Price'),
                            TableColumn::make('Total'),
                        ])
                            ->label('Sold Items')
                            // ->relationship('saleItems')  // ← uses the relationship
                            ->schema([
                                TextEntry::make('item.name')
                                    ->label('Item Name')
                                    ->weight('bold'),

                                TextEntry::make('quantity')
                                    ->label('Quantity')
                                    ->numeric(),

                                TextEntry::make('price')
                                    ->label('Unit Price')
                                    ->money('TSH'),

                                TextEntry::make('total')
                                    ->label('Line Total')
                                    ->money('TSH')
                                    ->weight('bold'),
                            ])
                            ->columns(4)
                            ->grid(2),               // optional: 2 columns per row
                            // ->collapsible()
                            // ->collapsed(false)      // open by default
                            // ->emptyLabel('No items sold')
                            // ->extraAttributes(['class' => 'border rounded-lg p-4 bg-gray-50']),

                        // Summary total
                        Placeholder::make('total_summary')
                            ->label('Grand Total')
                            ->content(function ($record) {
                                return 'TSH ' . number_format($record->total, 2);
                            })
                            ->extraAttributes(['class' => 'text-xl font-bold text-primary-600 mt-4']),
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
                                    ->icon('heroicon-o-plus-circle'),
                                TextEntry::make('updated_at')
                                    ->label('Last Updated')
                                    ->dateTime('M j, Y - g:i A')
                                    ->icon('heroicon-o-arrow-path'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('creator.name')
                                    ->label('Created By')
                                    ->icon('heroicon-o-user')
                                    ->badge()
                                    ->default('System'),
                            ]),
                    ])
                    ->collapsed()
                    ->collapsible(),
            ]);
    }
}
