<?php

namespace App\Filament\Resources\AssemblyItems\Schemas;

use App\Models\Item;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Support\Enums\FontWeight;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;

class AssemblyItemInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
            // Hero Section with Image and Key Info
            Flex::make([
                Section::make()
                    ->schema([
                        ImageEntry::make('image')
                            ->hiddenLabel()
                            ->height(200)
                            ->defaultImageUrl(url('images/items/icons-default-image.png'))
                            ->extraImgAttributes(['class' => 'rounded-lg object-cover']),
                    ])
                    ->grow(true),

                Section::make()
                    ->schema([
                        TextEntry::make('name')
                            // ->size(TextEntry\TextEntrySize::Large)
                            ->weight(FontWeight::Bold),
                        TextEntry::make('description')
                            ->prose()
                            ->markdown()
                            ->color('gray')
                            ->default('No description available'),
                        Group::make([
                            TextEntry::make('status')
                                ->badge()
                                ->color(fn (string $state): string => match ($state) {
                                    'in_stock' => 'success',
                                    'low_stock' => 'warning',
                                    'out_of_stock' => 'danger',
                                    default => 'gray',
                                }),
                            IconEntry::make('is_active')
                                ->label('Active')
                                ->boolean()
                                ->trueIcon('heroicon-o-check-badge')
                                ->falseIcon('heroicon-o-x-circle')
                                ->trueColor('success')
                                ->falseColor('danger'),
                        ])->columns(2),
                    ]),
            ])->from('lg'),

            // Product Details Section
            Section::make('Product Details')
                ->icon('heroicon-o-cube')
                ->description('Basic product information and categorization')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextEntry::make('category.name')
                                ->label('Category')
                                ->icon('heroicon-o-tag')
                                ->badge()
                                ->color('primary')
                                ->default('Uncategorized'),
                            TextEntry::make('sku')
                                ->label('SKU')
                                ->icon('heroicon-o-qr-code')
                                ->copyable()
                                ->default('N/A'),


                            TextEntry::make('barcode')
                                ->icon('heroicon-o-bars-3-bottom-left')
                                ->copyable()
                                ->default('N/A'),

                                TextEntry::make('unit.name')
                                ->label('Unit of Measure')
                                ->icon('heroicon-o-scale')
                                ->badge()
                                ->default('N/A'),
                        ]),

                ])
                // ->columns(2)
                ->collapsible(),

            // Pricing & Inventory Section
            Section::make('Pricing & Inventory')
                ->icon('heroicon-o-currency-dollar')
                ->description('Pricing information and stock levels')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextEntry::make('cost_price')
                                ->label('Cost Price')
                                ->money('TSH')
                                // ->size(TextEntry\TextEntrySize::Large)
                                ->weight(FontWeight::Bold)
                                ->color('success'),
                            TextEntry::make('selling_price')
                                ->label('Selling Price')
                                ->money('TSH')
                                // ->size(TextEntry\TextEntrySize::Large)
                                ->weight(FontWeight::Bold)
                                ->color('success'),
                            TextEntry::make('discount')
                                ->suffix('%')
                                ->numeric(decimalPlaces: 0)
                                ->color('warning')
                                ->icon('heroicon-o-receipt-percent')
                                ->default('No discount'),
                            TextEntry::make('quantity')
                                ->numeric(decimalPlaces: 0)
                                ->icon('heroicon-o-cube-transparent')
                                ->badge()
                                ->color(fn (int $state): string => match (true) {
                                    $state > 100 => 'success',
                                    $state > 20 => 'warning',
                                    default => 'danger',
                                }),
                        ]),
                ])
                ->collapsible(),

            // Store Information
            Section::make('Store Information')
                ->icon('heroicon-o-building-storefront')
                ->schema([
                    TextEntry::make('store.name')
                        ->label('Store')
                        ->badge()
                        ->color('info')
                        ->default('N/A'),
                ])
                ->collapsible(),

            // Audit Trail Section (Collapsed by default)
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
                            TextEntry::make('deleted_at')
                                ->label('Deleted')
                                ->dateTime('M j, Y - g:i A')
                                ->icon('heroicon-o-trash')
                                ->color('danger')
                                ->visible(fn (Item $record): bool => $record->trashed()),
                        ]),
                    Grid::make(2)
                        ->schema([
                            TextEntry::make('creator.name')
                                ->label('Created By')
                                ->icon('heroicon-o-user')
                                ->badge()
                                ->default('System'),
                            TextEntry::make('updator.name')
                                ->label('Updated By')
                                ->icon('heroicon-o-user')
                                ->badge()
                                ->default('N/A'),
                            TextEntry::make('deletor.name')
                                ->label('Deleted By')
                                ->icon('heroicon-o-user')
                                ->badge()
                                ->color('danger')
                                ->visible(fn (Item $record): bool => $record->trashed()),
                        ]),
                ])
                ->collapsed()
                ->collapsible(),
        // ── NEW: Bill of Materials Section ──
                Section::make('Bill of Materials')
                    ->icon('heroicon-o-document-text')
                    ->description('Components used to assemble this product')
                    ->collapsible()
                    ->schema([
                        RepeatableEntry::make('billOfMaterial.items')
                            ->label('Components')
                            // ->grid(6)                       // ← Total 6 columns
                            // ->columnSpanFull()              // ← Full width of the section
                            ->schema([
                                TextEntry::make('component.name')
                                    ->label('Item / Component')
                                    ->columnSpan(2)             // ← Takes 3 columns (half the row)
                                    ->weight(FontWeight::Medium)
                                    ->icon('heroicon-o-cube'),

                                TextEntry::make('quantity')
                                    ->label('Quantity')
                                    ->columnSpan(1)
                                    ->numeric(decimalPlaces: 3)
                                    ->icon('heroicon-o-scale')
                                    ->badge(),

                                TextEntry::make('unit_cost')
                                    ->label('Unit Cost')
                                    ->columnSpan(1)
                                    ->money('TSH')
                                    ->color('gray'),

                                TextEntry::make('total_cost')
                                    ->label('Line Total')
                                    ->columnSpan(1)
                                    ->money('TSH')
                                    ->weight(FontWeight::Bold)
                                    ->color('success'),
                            ])
                            // ->grid(6)
                            ->columns(5)
                            ->columnSpanFull()
                            // ->emptyLabel('No components defined')
                            ->extraAttributes(['class' => 'border rounded-lg overflow-hidden']),

                        // Optional: Show batch & total cost summary
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('billOfMaterial.batch_quantity')
                                    ->label('Batch Quantity (Yield)')
                                    ->numeric()
                                    ->badge()
                                    ->color('info')
                                    ->icon('heroicon-o-beaker'),

                                TextEntry::make('billOfMaterial.total_cost')
                                    ->label('Total BOM Cost')
                                    ->money('TSH')
                                    ->weight(FontWeight::Bold)
                                    ->size('lg')
                                    ->color('primary'),
                            ])

                            ->visible(fn (Item $record) => $record->billOfMaterial?->exists ?? false),
                    ])
                    ->columnSpanFull()
                    ->visible(fn (Item $record) => $record->billOfMaterial?->items->isNotEmpty() ?? false),
            ]);
    }
}
