<?php

namespace App\Filament\Resources\Expenses\Pages;

use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use App\Filament\Exports\ExpenseExporter;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\Expenses\ExpenseResource;

class ListExpenses extends ListRecords
{
    protected static string $resource = ExpenseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            ExportAction::make()
                ->exporter(ExpenseExporter::class),
        ];
    }
}
