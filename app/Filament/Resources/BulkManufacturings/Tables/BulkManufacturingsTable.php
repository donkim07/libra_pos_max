<?php

namespace App\Filament\Resources\BulkManufacturings\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;

class BulkManufacturingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('item.name')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('remaining_quantity')
                    ->label('Remaining Qty')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('store.name')
                    ->sortable(),
                TextColumn::make('total_cost')
                    ->money('TSH')
                    ->sortable(),
                // IconColumn::make('is_finished')
                //     ->label('Finished')
                //     ->boolean()
                //     ->sortable(),
                TextColumn::make('is_finished')
    ->label('Finished')
    ->sortable()
    ->html()
    ->formatStateUsing(function (bool $state, $record) {

        $finishedIcon = Blade::render(
            $state
                ? '<x-heroicon-o-check-circle class="w-5 h-5 text-success-600 inline" />'
                : '<x-heroicon-o-x-circle class="w-5 h-5 text-danger-600 inline" />'
        );

        $wasteIcon = '';

        if ($state && $record->waste_quantity > 0) {
            $wasteIcon = Blade::render(
                '<span title="Waste detected: ' . $record->waste_quantity . '">
                    <x-heroicon-o-exclamation-triangle class="w-5 h-5 text-warning-500 inline ml-1" />
                </span>'
            );
        }

        return new HtmlString($finishedIcon . $wasteIcon);
    }),
                TextColumn::make('creator.name')
                    ->label('Created By')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('date_manufactured')
                ->date()
                ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
