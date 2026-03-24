<?php

namespace App\Filament\Resources\Purchases\Tables;

use App\Models\User;
use App\Models\Store;
use App\Models\Customer;
use App\Models\Supplier;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Tables\Filters\Filter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;

class PurchasesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('supplier.name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('total')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('paid_amount')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('discount')
                    ->numeric()
                    ->sortable(),
                // TextColumn::make('status')
                //     ->badge(),
                TextColumn::make('account.name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('creator.name')
                    ->searchable()
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
            ->defaultSort('created_at', 'desc')

            ->filters([

    // 📅 Date range filter
    Filter::make('created_at')
        ->form([
            DatePicker::make('from')
                ->label('From'),
            DatePicker::make('until')
                ->label('Until'),
        ])
        ->query(function (Builder $query, array $data) {
            return $query
                ->when(
                    $data['from'],
                    fn ($query) => $query->whereDate('created_at', '>=', $data['from'])
                )
                ->when(
                    $data['until'],
                    fn ($query) => $query->whereDate('created_at', '<=', $data['until'])
                );
        }),

    // 🏪 Store filter
    // SelectFilter::make('store_id')
    //     ->label('Store')
    //     // ->relationship('store', 'name')
    //     ->options(fn () => Store::pluck('name', 'id')->toArray())
    //     ->searchable()
    //     ->preload(),

    // 👤 Supplier filter
    SelectFilter::make('supplier_id')
        ->label('Supplier')
        ->options(fn () => Supplier::pluck('name', 'id')->toArray())
        ->searchable()
        ->preload(),

    // 🧑‍💼 Cashier / Creator filter
    SelectFilter::make('created_by')
        ->label('Created By')
        // ->relationship('creator', 'name')
        ->options(
            fn () => User::pluck('name', 'id')->toArray()
        )
        ->searchable()
        ->preload(),

    // // 💳 Payment status
    // SelectFilter::make('payment_status')
    //     ->options([
    //         'paid' => 'Paid',
    //         'partial' => 'Partial',
    //         'unpaid' => 'Unpaid',
    //     ]),

    // 💰 Total amount range
    Filter::make('total')
        ->form([
            TextInput::make('min')
                ->label('Min Total')
                ->numeric(),
            TextInput::make('max')
                ->label('Max Total')
                ->numeric(),
        ])
        ->query(function (Builder $query, array $data) {
            return $query
                ->when(
                    $data['min'],
                    fn ($query) => $query->where('total', '>=', $data['min'])
                )
                ->when(
                    $data['max'],
                    fn ($query) => $query->where('total', '<=', $data['max'])
                );
        }),

])
->filtersFormColumns(2)
->deferFilters(false)
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
