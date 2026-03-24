<?php

namespace App\Filament\Resources\StockMovements\Tables;

use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;

class StockMovementsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->striped()
            ->columns([
                TextColumn::make('item.name')
                    ->numeric()
                    ->sortable(),
                    TextColumn::make('sourceStore.name')
                    ->sortable(),
                    TextColumn::make('quantity')
                        ->label('Qnty on Source')
                        ->numeric()
                        ->sortable(),
                TextColumn::make('destinationStore.name')
                    ->sortable(),
                TextColumn::make('quantity')
                    ->label('Quantity Sent')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('reference_code')
                    ->searchable(),
                // TextColumn::make('reference_id')
                //     ->numeric()
                //     ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
