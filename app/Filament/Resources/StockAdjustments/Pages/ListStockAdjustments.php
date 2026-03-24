<?php

namespace App\Filament\Resources\StockAdjustments\Pages;

use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Exports\StockAdjustmentExporter;
use App\Filament\Resources\StockAdjustments\StockAdjustmentResource;

class ListStockAdjustments extends ListRecords
{
    protected static string $resource = StockAdjustmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            ExportAction::make()
                ->exporter(StockAdjustmentExporter::class),
        ];
    }
}
