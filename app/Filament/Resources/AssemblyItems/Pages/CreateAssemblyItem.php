<?php

namespace App\Filament\Resources\AssemblyItems\Pages;

use App\Filament\Resources\AssemblyItems\AssemblyItemResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;
use App\Models\Item;

class CreateAssemblyItem extends CreateRecord
{
    protected static string $resource = AssemblyItemResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Optional: Ensure item_type is always 'assembly'
        $data['item_type'] = 'assembly';

        // Optional: Validate at least one component if required
        $bomData = $data['billOfMaterial'] ?? [];
        if (empty($bomData['items'] ?? [])) {
            // You can throw validation or set default
            // For now, allow empty BOM (total_cost=0)
        }

        // Optional: Pre-set total_cost to 0 if not calculated yet
        if (isset($data['billOfMaterial'])) {
            $data['billOfMaterial']['total_cost'] = 0;
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        DB::transaction(function () {
            /** @var Item $assemblyItem */
            $assemblyItem = $this->record;

            // Ensure BOM exists and recalculate (covers any timing issues)
            if ($bom = $assemblyItem->billOfMaterial) {
                $bom->recalculateCosts();
            }

            // Optional: If you later add inventory/production logic here (e.g., initial stock adjustment)
            // $assemblyItem->quantity += $bom?->batch_quantity ?? 0; // or handle in manufacturing
        });
    }
}
