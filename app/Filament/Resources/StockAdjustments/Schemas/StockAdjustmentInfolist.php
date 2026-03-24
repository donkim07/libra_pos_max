<?php

namespace App\Filament\Resources\StockAdjustments\Schemas;

use App\Models\StockAdjustment; // ← adjust if model name is different
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\IconPosition;

class StockAdjustmentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // ── Hero / Summary Section ──
                Section::make('Adjustment Overview')
                    ->icon('heroicon-o-document-plus')
                    ->description('Core details of this stock movement')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('type')
                                    ->label('Adjustment Type')
                                    ->badge()
                                    ->color(fn (string $state): string => match (strtolower($state)) {
                                        'addition', 'add', 'in', 'receive' => 'success',
                                        'reduction', 'subtract', 'out', 'issue' => 'danger',
                                        'correction', 'adjustment' => 'warning',
                                        default => 'gray',
                                    })
                                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                                    ->icon(fn (string $state): string => match (strtolower($state)) {
                                        default => 'heroicon-o-plus-circle', // fallback
                                    })
                                    ->iconPosition(IconPosition::Before)
                                    ->weight(FontWeight::Bold),

                                TextEntry::make('quantity_change')
                                    ->label('Quantity Change')
                                    ->numeric(decimalPlaces: 0)
                                    ->prefix(fn (float|int $state): string => $state >= 0 ? '+' : '')
                                    ->color(fn (float|int $state): string => $state >= 0 ? 'success' : 'danger')
                                    ->weight(FontWeight::Bold)
                                    ->icon('heroicon-o-scale')
                                    ->iconPosition(IconPosition::Before),
                            ]),
                    ])
                    ->collapsible(),

                // ── Item & Location ──
                Section::make('Item & Location')
                    ->icon('heroicon-o-cube')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('item.name')
                                    ->label('Item')
                                    ->icon('heroicon-o-cube-transparent')
                                    ->weight(FontWeight::Medium),
                                    // ->url(fn (StockAdjustment $record) => $record->item ? route('filament.resources.items.view', $record->item) : null),

                                TextEntry::make('store.name')
                                    ->label('Store / Warehouse')
                                    ->icon('heroicon-o-building-storefront')
                                    ->badge()
                                    ->color('info'),
                                    // ->url(fn (StockAdjustment $record) => $record->store ? route('filament.resources.stores.view', $record->store) : null),

                            
                            ]),
                    ])
                    ->collapsible(),

                // ── Before / After Snapshot ──
                Section::make('Stock Snapshot')
                    ->icon('heroicon-o-chart-bar-square')
                    ->description('Stock level before and after adjustment')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('quantity_before')
                                    ->label('Before Adjustment')
                                    ->numeric()
                                    ->badge()
                                    ->color('gray'),

                                TextEntry::make('quantity_change')
                                    ->label('Change')
                                    ->numeric()
                                    ->prefix(fn (float|int $state): string => $state >= 0 ? '+' : '')
                                    ->color(fn (float|int $state): string => $state >= 0 ? 'success' : 'danger')
                                    ->weight(FontWeight::Bold),

                                TextEntry::make('quantity_after')
                                    ->label('After Adjustment')
                                    ->numeric()
                                    ->badge()
                                    ->color(fn (StockAdjustment $record): string => $record->quantity_after > 0 ? 'success' : 'danger'),
                            ]),
                    ])
                    ->collapsible(),

                // ── Reason & Context ──
                Section::make('Reason & Reference')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->schema([
                        TextEntry::make('reason')
                            ->label('Reason / Note')
                            ->prose()
                            ->markdown()
                            ->placeholder('No reason provided')
                            ->columnSpanFull(),

                        Grid::make(2)
                            ->schema([
                                TextEntry::make('sale.name') // or order_number, invoice etc.
                                    ->label('Related Sale')
                                    ->icon('heroicon-o-shopping-cart')
                                    ->badge()
                                    ->color('warning')
                                    ->placeholder('—')
                                    ->url(fn (StockAdjustment $record) => $record->sale ? route('filament.resources.sales.view', $record->sale) : null)
                                    ->visible(fn (StockAdjustment $record): bool => filled($record->sale_id)),
                            ]),
                    ])
                    ->collapsible(),

                // ── Audit Trail ──
                Section::make('Audit Trail')
                    ->icon('heroicon-o-clock')
                    ->description('Who did this and when')
                    ->collapsed()
                    ->collapsible()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('creator.name')
                                    ->label('Performed By')
                                    ->icon('heroicon-o-user')
                                    ->badge()
                                    ->default('System')
                                    ->placeholder('—'),

                                TextEntry::make('created_at')
                                    ->label('Performed At')
                                    ->dateTime('M j, Y - g:i A')
                                    ->icon('heroicon-o-calendar-days'),

                                TextEntry::make('updated_at')
                                    ->label('Last Modified')
                                    ->dateTime('M j, Y - g:i A')
                                    ->icon('heroicon-o-arrow-path')
                                    ->placeholder('Never modified'),
                            ]),
                    ]),
            ]);
    }
}
