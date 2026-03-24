<?php

namespace App\Filament\Resources\SaleOrders\Tables;

use App\Models\SaleOrder;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Notifications\Notification;

class SaleOrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('customer.name')
                    ->numeric()
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
                TextColumn::make('status')
                    ->searchable()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'completed'     => 'success',
                        'pending' => 'danger',
                        default        => 'gray',
                    }),
                TextColumn::make('payment_status')
                    ->badge()
                    ->searchable()
                    ->color(fn (string $state): string => match ($state) {
                        'full_payment'     => 'success',
                        'partial_payment' => 'warning',
                        'unpaid'        => 'danger',
                    }),
                TextColumn::make('receipt_number')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('account.name')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('order_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('expected_delivery_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('delivery_status')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('creator.name')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                // TextColumn::make('updator.name')
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
            ->filters([
                //
            ])
            ->recordActions([
                // Fulfill action - only shown when appropriate
            // Action::make('fulfill')
            //     ->label('Fulfill Order')
            //     ->color('success')
            //     ->icon('heroicon-o-check-circle')
            //     ->requiresConfirmation()
            //     ->modalHeading('Fulfill Sale Order')
            //     ->modalDescription('This will deduct stock and mark the order as delivered/completed. Continue?')
            //     ->modalSubmitActionLabel('Yes, Fulfill')
            //     ->visible(fn (SaleOrder $record): bool =>
            //         $record->status === 'pending' &&
            //         $record->delivery_status === 'pending'
            //     )
            //     ->action(function () {
            //         $this->fulfillOrder($this->record);
            //         Notification::make()
            //             ->title('Order fulfilled successfully')
            //             ->success()
            //             ->send();
            //     }),
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
