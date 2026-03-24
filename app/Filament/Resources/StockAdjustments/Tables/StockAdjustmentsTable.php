<?php

namespace App\Filament\Resources\StockAdjustments\Tables;

use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;

class StockAdjustmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->striped()
            ->columns([
                TextColumn::make('item.name')
                    ->label('Item Name')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('store.name')
                    ->numeric()
                    ->sortable(),
                // TextColumn::make('manufacturing_id')
                //     ->numeric()
                //     ->sortable(),
                TextColumn::make('type')
                    ->sortable()
                    ->color(fn (string $state): string => match ($state) {
                        'increase' => 'success',
                        'decrease' => 'danger',
                        default => 'secondary',
                    })
                    ->badge(),
                TextColumn::make('quantity_change')
                    ->numeric()
                    ->sortable(),
                // TextColumn::make('quantity_before')
                //     ->numeric()
                //     ->sortable(),
                // TextColumn::make('quantity_after')
                //     ->numeric()
                //     ->sortable(),
                TextColumn::make('creator.name')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                // TextColumn::make('sale_id')
                //     ->numeric()
                //     ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                // EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
