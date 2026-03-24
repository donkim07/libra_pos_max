<?php

namespace App\Filament\Exports;

use App\Models\StockAdjustment;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class StockAdjustmentExporter extends Exporter
{
    protected static ?string $model = StockAdjustment::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('item.name')
                ->label('Item Name'),
            ExportColumn::make('store.name')
                ->label('Store Name'),
            ExportColumn::make('type'),
            ExportColumn::make('quantity_change'),
            ExportColumn::make('quantity_before'),
            ExportColumn::make('quantity_after'),
            ExportColumn::make('reason'),
            ExportColumn::make('creator.name')
                ->label('Created By'),
            ExportColumn::make('created_at'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your stock adjustment export has completed and ' . Number::format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
