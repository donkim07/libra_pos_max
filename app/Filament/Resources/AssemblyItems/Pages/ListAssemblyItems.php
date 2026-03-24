<?php

namespace App\Filament\Resources\AssemblyItems\Pages;

use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ImportAction;
use App\Filament\Exports\ItemExporter;
use App\Filament\Imports\ItemImporter;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\AssemblyItems\AssemblyItemResource;

class ListAssemblyItems extends ListRecords
{
    protected static string $resource = AssemblyItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            ImportAction::make()
                ->importer(ItemImporter::class),
            ExportAction::make()
                ->exporter(ItemExporter::class),
        ];
    }
}
