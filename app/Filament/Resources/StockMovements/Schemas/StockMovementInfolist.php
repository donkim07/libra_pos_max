<?php

namespace App\Filament\Resources\StockMovements\Schemas;

use App\Models\StockMovement; // ← adjust if your model name differs
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\IconPosition;

class StockMovementInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // ── Hero / Summary ──
                Section::make('Movement Summary')
                    ->icon('heroicon-o-arrow-path')
                    ->description('Key details of this stock transaction')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('item.name')
                                    ->label('Item')
                                    ->icon('heroicon-o-cube-transparent')
                                    ->weight(FontWeight::Medium)
                                    ->columnSpan(2),

                                // TextEntry::make('type')
                                //     ->label('Movement Type')
                                //     ->badge()
                                //     ->formatStateUsing(fn (string $state): string => ucfirst(str_replace('_', ' ', $state)))
                                //     ->color(fn (string $state): string => match (strtolower($state)) {
                                //         'in', 'addition', 'receipt', 'return' => 'success',
                                //         'out', 'reduction', 'issue', 'sale', 'consumption' => 'danger',
                                //         'transfer', 'move' => 'purple',
                                //         'adjustment', 'correction' => 'warning',
                                //         default => 'gray',
                                //     })
                                //     ->icon(fn (string $state): string => match (strtolower($state)) {
                                //         'in', 'addition', 'receipt' => 'heroicon-o-arrow-down-circle',
                                //         'out', 'reduction', 'issue' => 'heroicon-o-arrow-up-circle',
                                //         'transfer' => 'heroicon-o-arrows-right-left',
                                //         'adjustment' => 'heroicon-o-wrench-screwdriver',
                                //         default => 'heroicon-o-arrows-up-down',
                                //     })
                                //     ->iconPosition(IconPosition::Before)
                                //     ->weight(FontWeight::Bold),
                            ]),
                    ])
                    ->collapsible(),

                // ── Quantity & Value ──
                Section::make('Quantity & Direction')
                    ->icon('heroicon-o-scale')
                    ->schema([
                        Grid::make(3)
                            ->schema([


                                TextEntry::make('sourceStore.name')
                                    ->label('Source Store')
                                    ->color('gray')
                                    ->placeholder('—'),

                                TextEntry::make('quantity')
                                    ->label('Quantity Moved')
                                    ->numeric(decimalPlaces: 3)
                                    ->weight(FontWeight::Bold)
                                    ->color(fn (float|int $state): string => $state > 0 ? 'success' : 'danger')
                                    ->prefix(fn (float|int $state): string => $state > 0 ? '+' : '')
                                    ->icon('heroicon-o-cube')
                                    ->iconPosition(IconPosition::Before),

                                TextEntry::make('destinationStore.name')
                                    ->label('Destination Store')
                                    ->weight(FontWeight::Bold)
                                    ->color('primary')
                                    ->placeholder('—'),
                            ]),
                    ])
                    ->collapsible(),

                // ── Source / Reference ──
                Section::make('Reference / Source')
                    ->icon('heroicon-o-link')
                    ->description('Document of this movement')
                    ->schema([
                        Grid::make(2)
                            ->schema([


                                TextEntry::make('reference_code') // or reference_number, invoice_number, etc.
                                    ->label('Reference Document')
                                    ->icon('heroicon-o-document-text')
                                    ->badge()
                                    ->color('warning')
                                    ->placeholder('—'),


                                    // ->visible(fn (StockMovement $record): bool => filled($record->reference_id)),
                            ]),
                    ])
                    ->collapsible(),

                // ── Audit Trail ──
                Section::make('Audit Trail')
                    ->icon('heroicon-o-clock')
                    ->description('When and by whom')
                    // ->collapsed()
                    ->collapsible()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('created_at')
                                    ->label('Occurred At')
                                    ->dateTime('M j, Y - g:i A')
                                    ->icon('heroicon-o-calendar-days'),

                                TextEntry::make('updated_at')
                                    ->label('Last Modified')
                                    ->dateTime('M j, Y - g:i A')
                                    ->icon('heroicon-o-arrow-path')
                                    ->placeholder('Never modified'),

                                TextEntry::make('creator.name')
                                    ->label('Performed By')
                                    ->icon('heroicon-o-user')
                                    ->badge()
                                    ->default('System')
                                    ->placeholder('—'),
                            ]),
                    ]),
            ]);
    }
}
