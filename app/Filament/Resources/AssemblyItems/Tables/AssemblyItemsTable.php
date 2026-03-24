<?php

namespace App\Filament\Resources\AssemblyItems\Tables;

use App\Models\Item;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
// use Filament\Forms\Components\Builder;
use Illuminate\Support\Facades\DB;

use Filament\Actions\BulkActionGroup;
use App\Filament\Exports\ItemExporter;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ExportBulkAction;
use Filament\Actions\RestoreBulkAction;
// use Illuminate\Container\Attributes\DB;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Actions\ForceDeleteBulkAction;
// use Illuminate\Container\Attributes\DB;

class AssemblyItemsTable
{

protected function getTableQuery(): \Illuminate\Database\Eloquent\Builder
{
    return Item::query()
        ->where('item_type', 'assembly')
        ->select('items.*')
        ->selectSub(
            DB::table('item_store')
                ->selectRaw('COALESCE(SUM(quantity), 0)')
                ->whereColumn('item_store.item_id', 'items.id'),
            'total_stock'
        );
}
    public static function configure(Table $table): Table
    {
        return $table
             //
        ->striped()
        // ->deferLoading()
        ->modifyQueryUsing(fn (Builder $query) => $query->where('item_type', 'assembly'))
        ->recordUrl(false)
            ->recordTitleAttribute('name')
            ->columns([
                ImageColumn::make('image') // Optional: add image preview in table
                    ->height(48)
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->square(),
                TextColumn::make('name')
                    ->searchable()
                    ->wrap(),
                TextColumn::make('unit.name')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                TextColumn::make('category.name')
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->sortable(),
                TextColumn::make('cost_price')
                    ->money('TZS')
                    ->sortable(),
                TextColumn::make('selling_price')
                    ->money('TZS')
                    ->sortable(),
                TextColumn::make('total_stock')
                    ->label('On Hand')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('status')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'in_stock'     => 'success',
                        'out_of_stock' => 'danger',
                        'pre_order'    => 'warning',
                        default        => 'gray',
                    }),
                TextColumn::make('store.name')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('creator.name')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updator.name')
                    ->label('Updated By')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deletor.name')
                    ->label('Deleted By')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->reorderableColumns()
            ->deferColumnManager(false)
            ->filters([

                SelectFilter::make('status')
                    ->options([
                        'in_stock'     => 'In Stock',
                        'out_of_stock' => 'Out of Stock',
                    ])
                    ->multiple(),
                TernaryFilter::make('is_active')
                    ->label('Active')
                    ->placeholder('Any')
                    ->trueLabel('Is active')
                    ->falseLabel('Is not active'),
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ExportBulkAction::make()
                        ->exporter(ItemExporter::class),

                ]),
            ]);
    }
}
