<?php

namespace App\Filament\Imports;

use App\Models\BillOfMaterialItem;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class BillOfMaterialItemImporter extends Importer
{
    protected static ?string $model = BillOfMaterialItem::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('bill_of_material_id')
                // ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer']),
            ImportColumn::make('item_id')
                // ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer']),
            ImportColumn::make('quantity')
                // ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer']),
            ImportColumn::make('unit_id')
                ->numeric()
                ->rules(['integer']),
            ImportColumn::make('unit_cost')
                // ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer']),
            ImportColumn::make('total_cost')
                // ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer']),
            ImportColumn::make('created_by')
                ->numeric()
                ->rules(['integer']),
            ImportColumn::make('updated_by')
                ->numeric()
                ->rules(['integer']),
            // ImportColumn::make('deleted_by')
            //     ->numeric()
            //     ->rules(['integer']),
        ];
    }

    public function resolveRecord(): BillOfMaterialItem
    {
        return BillOfMaterialItem::firstOrNew([
            'id' => $this->data['id'],
        ]);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your bill of material item import has completed and ' . Number::format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
