<?php

namespace App\Filament\Exports;

use App\Models\Item;
use Filament\Actions\Action;
use Illuminate\Support\Number;
use Filament\Actions\Exports\Exporter;
use Filament\Notifications\Notification;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Models\Export;

class ItemExporter extends Exporter
{
    protected static ?string $model = Item::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('name'),
            ExportColumn::make('description'),
            ExportColumn::make('category.name'),
            ExportColumn::make('sku')
                ->label('SKU'),
            ExportColumn::make('barcode'),
            ExportColumn::make('unit.name'),
            ExportColumn::make('cost_price'),
            ExportColumn::make('selling_price'),
            // ExportColumn::make('discount'),
            ExportColumn::make('quantity'),
            ExportColumn::make('status'),
            ExportColumn::make('is_active'),
            ExportColumn::make('store.name'),
            ExportColumn::make('image'),
            ExportColumn::make('creator.name')
                ->label('Created By'),
            ExportColumn::make('created_at'),
            ExportColumn::make('item_type'),

        ];
        Notification::make()
                        ->title('Exported successfully')
                        ->success()
                        ->actions([
                            Action::make('view')
                                ->button()
                                ->markAsRead(),
                        ])
                        ->send();
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your item export has completed and ' . Number::format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
