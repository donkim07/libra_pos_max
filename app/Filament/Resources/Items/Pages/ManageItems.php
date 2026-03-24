<?php

namespace App\Filament\Resources\Items\Pages;

use App\Models\Item;
use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ImportAction;
use App\Filament\Exports\ItemExporter;
use App\Filament\Imports\ItemImporter;
use Illuminate\Database\Eloquent\Model;
use Filament\Resources\Pages\ManageRecords;
use App\Filament\Resources\Items\ItemResource;

class ManageItems extends ManageRecords
{
    protected static string $resource = ItemResource::class;

    // This runs ONLY after a new record is created
    //     protected function afterCreate(): void
    // {
    //     $item = $this->record;
    //     $data = $this->form->getState();
    //     $this->syncStoreQuantities($item, $data);
    // }

    // protected function afterSave(): void
    // {
    //     $item = $this->record;
    //     $data = $this->form->getState();
    //     $this->syncStoreQuantities($item, $data);
    // }

    // protected function syncStoreQuantities(Item $item, array $data): void
    // {
    //     $storeQuantities = $data['store_quantities'] ?? [];

    //     if (empty($storeQuantities)) {
    //         return;
    //     }

    //     foreach ($storeQuantities as $storeId => $qty) {
    //         $qty = (float) $qty;

    //         // Update or create in item_store table
    //         $item->updateStockForStore((int) $storeId, $qty);
    //     }

    //     // Update the item's total quantity and status
    //     $item->refresh();
    //     $totalQty = $item->getTotalStockAttribute();
    //     $newStatus = $totalQty > 0 ? 'in_stock' : 'out_of_stock';

    //     $item->updateQuietly([
    //         'quantity' => $totalQty,
    //         'status' => $newStatus
    //     ]);
    // }

    protected function getHeaderActions(): array
    {

        return [
            CreateAction::make()
            ->using(function (array $data): Model {
                    // Create the item
                    $item = Item::create($data);

                    // Save quantity to item_store table
                    if (isset($data['store_id']) && isset($data['quantity'])) {
                        $item->stores()->attach($data['store_id'], [
                            'quantity' => $data['quantity'],
                        ]);
                    }

                    return $item;
                })
                ->after(function (Item $record, array $data) {
                    // Alternative: you can also handle it here
                    // This runs after the record is created
                }),
            ImportAction::make()
                ->importer(ItemImporter::class),
            ExportAction::make()
                ->exporter(ItemExporter::class),


        ];
    }

        protected function handleRecordUpdate(Model $record, array $data): Model
    {
        // Update the item
        $record->update($data);

        // Update or create the item_store record
        if (isset($data['store_id']) && isset($data['quantity'])) {
            $record->stores()->syncWithoutDetaching([
                $data['store_id'] => ['quantity' => $data['quantity']]
            ]);
        }

        return $record;
    }
    
}
