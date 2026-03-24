<?php

namespace App\Filament\Resources\Sales\Tables;

use App\Models\User;
use App\Models\Store;
use App\Models\Customer;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use Filament\Tables\Filters\Filter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Widgets\TopSoldItemsChart;

class SalesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->striped()
            ->columns([
                TextColumn::make('customer.name')
                    ->numeric()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('store.name')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('total')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('paid_amount')
                    ->numeric()
                    ->sortable(),
                // TextColumn::make('discount')
                //     ->numeric()
                //     ->sortable(),
                // TextColumn::make('status')
                //     ->badge(),
                // TextColumn::make('payment_status')
                //     ->searchable(),
                TextColumn::make('account.name')
                //  ->label('Acc')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('receipt_date')
                    ->sortable(),
                // TextColumn::make('account_id')
                //     ->numeric()
                //     ->sortable(),
                TextColumn::make('creator.name')
                    ->searchable()
                    ->formatStateUsing(fn ($record) =>
                        $record->creator?->FirstName() ?? '—'
                    )
                    ->sortable(),
                // TextColumn::make('updator.name')
                //     ->searchable()
                //     ->sortable()
                //     ->toggleable(isToggledHiddenByDefault: true),
                // TextColumn::make('deletor.name')
                //     ->numeric()
                //     ->sortable()
                //     ->toggleable(isToggledHiddenByDefault: true),
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
    Filter::make('receipt_date')
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
                    fn ($query) => $query->whereDate('receipt_date', '>=', $data['from'])
                )
                ->when(
                    $data['until'],
                    fn ($query) => $query->whereDate('receipt_date', '<=', $data['until'])
                );
        }),

    // 🏪 Store filter
    SelectFilter::make('store_id')
        ->label('Store')
        // ->relationship('store', 'name')
        ->options(fn () => Store::pluck('name', 'id')->toArray())
        ->searchable()
        ->preload(),

    // 👤 Customer filter
    SelectFilter::make('customer_id')
        ->label('Customer')
        ->relationship('customer', 'name')
        ->options(fn () => Customer::pluck('name', 'id')->toArray())
        ->searchable()
        ->preload(),

    // 🧑‍💼 Cashier / Creator filter
    SelectFilter::make('created_by')
        ->label('Created By')
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
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);

    }


}




