<?php

namespace App\Filament\Resources\StockMovements\Pages;

use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Exports\StockMovementExporter;
use App\Filament\Resources\StockMovements\StockMovementResource;

class ListStockMovements extends ListRecords
{
    protected static string $resource = StockMovementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            ExportAction::make()
                ->exporter(StockMovementExporter::class),
        ];
    }
}
