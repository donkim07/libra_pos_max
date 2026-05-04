<?php

namespace App\Filament\Resources\BulkManufacturings\Pages;

use App\Filament\Resources\BulkManufacturings\BulkManufacturingResource;
use App\Filament\Resources\BulkManufacturings\Widgets\BulkManufacturingTableStats;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBulkManufacturings extends ListRecords
{
    protected static string $resource = BulkManufacturingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            BulkManufacturingTableStats::class,
        ];
    }
}
