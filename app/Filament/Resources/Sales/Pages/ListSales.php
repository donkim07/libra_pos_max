<?php

namespace App\Filament\Resources\Sales\Pages;

use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use App\Filament\Exports\SaleExporter;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\Sales\SaleResource;

class ListSales extends ListRecords
{
    protected static string $resource = SaleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            ExportAction::make()
                ->exporter(SaleExporter::class),
        ];
    }


}
