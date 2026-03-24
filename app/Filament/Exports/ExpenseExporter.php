<?php

namespace App\Filament\Exports;

use App\Models\Expense;
use Filament\Actions\Action;
use Illuminate\Support\Number;
use Illuminate\Support\Facades\Auth;
use Filament\Actions\Exports\Exporter;
use Filament\Notifications\Notification;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Models\Export;

class ExpenseExporter extends Exporter
{
    protected static ?string $model = Expense::class;

    public static function getColumns(): array
    {
        return [
            // ExportColumn::make('id')
            //     ->label('ID'),
            ExportColumn::make('title'),
            ExportColumn::make('description'),
            ExportColumn::make('amount'),
            ExportColumn::make('account.name'),
            ExportColumn::make('store.name'),
            ExportColumn::make('incurred_on'),
            ExportColumn::make('creator.name')
                ->label('Created By'),
            ExportColumn::make('created_at'),
        ];
    }

    //

    public static function getCompletedNotificationBody(Export $export): string
    {

        $body = 'Your expense export has completed and ' . Number::format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }



        return $body;


    }
}
