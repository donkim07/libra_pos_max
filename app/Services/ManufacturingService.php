<?php

namespace App\Services;

use App\Models\Item;
use App\Models\Manufacturing;
use App\Models\StockAdjustment;
use App\Models\ManufacturingItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class ManufacturingService
{

public function create(array $data): Manufacturing
{
    return DB::transaction(function () use ($data) {

        $assemblyItem = Item::findOrFail($data['item_id']);
        $bom = $assemblyItem->billOfMaterial;

        if (!$bom) {
            throw ValidationException::withMessages([
                'item_id' => 'This assembly item has no Bill of Materials defined.',
            ]);
        }

        $manufactureQty = (float) $data['quantity'];
        $storeId = $data['store_id'];

        $manufacturing = Manufacturing::create([
            'item_id'      => $assemblyItem->id,
            'quantity'     => $manufactureQty,
            'store_id'     => $storeId,
            'notes'        => $data['notes'] ?? null,
            'created_by'   => Auth::id(),
        ]);

        $totalCost = 0;

        // Process ingredients (deduct from store)
        foreach ($bom->items as $bomItem) {
            $component = $bomItem->component;
            if (!$component) continue;

            $qtyPerUnit = (float) $bomItem->quantity;
            $usedQty = $qtyPerUnit * $manufactureQty;

            $before = $component->getQuantityForStore($storeId);
            if ($usedQty > $before) {
                throw ValidationException::withMessages([
                    'ingredients' => "Insufficient stock for {$component->name}: need {$usedQty}, have {$before}.",
                ]);
            }

            $after = $before - $usedQty;
            $lineCost = $usedQty * ($component->cost_price ?? 0);
            $totalCost += $lineCost;

            ManufacturingItem::create([
                'manufacturing_id' => $manufacturing->id,
                'item_id'          => $component->id,
                'quantity'         => $usedQty,
                'unit_cost'        => $component->cost_price ?? 0,
                'total_cost'       => $lineCost,
            ]);

            // Deduct stock
            $component->updateStockForStore($storeId, $after);



            // 🔄 Recalculate total stock AFTER update
            $totalQuantity = DB::table('item_store')
                ->where('item_id', $assemblyItem->id)
                ->sum('quantity');

            // 🔁 Update item status if needed
            $newStatus = $totalQuantity > 0 ? 'in_stock' : 'out_of_stock';

            if ($assemblyItem->status !== $newStatus) {
                $assemblyItem->update(['status' => $newStatus]);
            }



            StockAdjustment::create([
                'item_id'         => $component->id,
                'store_id'        => $storeId,
                'manufacturing_id'=> $manufacturing->id,
                'type'            => 'decrease',
                'quantity_change' => -$usedQty,
                'quantity_before' => $before,
                'quantity_after'  => $after,
                'reason'          => 'Used in manufacturing #' . $manufacturing->id,
                'created_by'      => Auth::id(),
            ]);
        }

        // Add finished assembly to store
        $beforeAsm = $assemblyItem->getQuantityForStore($storeId);
        $afterAsm  = $beforeAsm + $manufactureQty;

        $assemblyItem->updateStockForStore($storeId, $afterAsm);

        StockAdjustment::create([
            'item_id'         => $assemblyItem->id,
            'store_id'        => $storeId,
            'manufacturing_id'=> $manufacturing->id,
            'type'            => 'increase',
            'quantity_change' => $manufactureQty,
            'quantity_before' => $beforeAsm,
            'quantity_after'  => $afterAsm,
            'reason'          => 'Manufactured in #' . $manufacturing->id,
            'created_by'      => Auth::id(),
        ]);

        $manufacturing->update(['total_cost' => $totalCost]);

        return $manufacturing;
    });
}
}
