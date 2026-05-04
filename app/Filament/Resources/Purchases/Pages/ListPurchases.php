<?php

namespace App\Filament\Resources\Purchases\Pages;

use App\Filament\Resources\Purchases\Widgets\PurchaseTableStats;
use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Exports\PurchaseExporter;
use App\Filament\Resources\Purchases\PurchaseResource;

class ListPurchases extends ListRecords
{
    protected static string $resource = PurchaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            ExportAction::make()
                ->exporter(PurchaseExporter::class),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            PurchaseTableStats::class,
        ];
    }
}
