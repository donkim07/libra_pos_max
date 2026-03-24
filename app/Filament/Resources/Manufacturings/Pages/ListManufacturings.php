<?php

namespace App\Filament\Resources\Manufacturings\Pages;

use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ImportAction;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Exports\ManufacturingExporter;
use App\Filament\Imports\BillOfMaterialItemImporter;
use App\Filament\Resources\Manufacturings\ManufacturingResource;

class ListManufacturings extends ListRecords
{
    protected static string $resource = ManufacturingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            // ImportAction::make()
            //     ->importer(BillOfMaterialItemImporter::class)
            //     ->chunkSize(500),
            // ExportAction::make()
            //     ->exporter(ManufacturingExporter::class),
        ];
    }
}
