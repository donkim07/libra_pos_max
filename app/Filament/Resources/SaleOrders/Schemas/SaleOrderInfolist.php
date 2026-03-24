<?php

namespace App\Filament\Resources\SaleOrders\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class SaleOrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('customer_id')
                    ->numeric(),
                TextEntry::make('store_id')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('total')
                    ->numeric(),
                TextEntry::make('paid_amount')
                    ->numeric(),
                TextEntry::make('status'),
                TextEntry::make('payment_status'),
                TextEntry::make('receipt_number'),
                TextEntry::make('payment_method_id')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('account_id')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('order_date')
                    ->date(),
                TextEntry::make('expected_delivery_date')
                    ->date()
                    ->placeholder('-'),
                TextEntry::make('delivery_status'),
                TextEntry::make('created_by')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('updated_by')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
