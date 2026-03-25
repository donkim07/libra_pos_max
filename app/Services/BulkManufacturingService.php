<?php

namespace App\Services;

use App\Models\BulkManufacturing;
use App\Models\BulkManufacturingDivision;
use App\Models\BulkManufacturingDivisionItem;
use App\Models\BulkManufacturingItem;
use App\Models\Item;
use App\Models\StockAdjustment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BulkManufacturingService
{
    public function create(array $data): BulkManufacturing
    {
        return DB::transaction(function () use ($data) {
            $bulkItem = Item::findOrFail($data['item_id']);
            $storeId = $data['store_id'];
            $quantity = (float) $data['quantity'];

            $bulk = BulkManufacturing::create([
                'item_id' => $bulkItem->id,
                'quantity' => $quantity,
                'date_manufactured' => $data['date_manufactured'],
                'store_id' => $storeId,
                'notes' => $data['notes'] ?? null,
                'created_by' => Auth::id(),
                'remaining_quantity' => $quantity,
                'is_finished' => false,
                'waste_quantity' => 0,
            ]);

            $totalCost = 0;

            $bulkItem->loadMissing('billOfMaterial.items');
            $hasBomComponents = $bulkItem->billOfMaterial?->items->isNotEmpty() ?? false;

            $ingredients = collect($data['ingredients'] ?? []);
            if ($hasBomComponents && $ingredients->isEmpty()) {
                throw ValidationException::withMessages([
                    'ingredients' => 'Add or confirm bill of materials lines for this bulk assembly before saving.',
                ]);
            }

            $bulkBomItemIds = $ingredients->pluck('item_id')->unique();

            foreach ($ingredients as $ing) {
                $compId = $ing['item_id'];
                $comp = Item::findOrFail($compId);

                $usedQty = (float) $ing['quantity'];
                $before = $comp->getQuantityForStore($storeId);

                if ($usedQty > $before) {
                    throw ValidationException::withMessages([
                        'ingredients' => "Insufficient stock for {$comp->name}: need {$usedQty}, have {$before}.",
                    ]);
                }

                $after = $before - $usedQty;
                $unitCost = (float) ($comp->cost_price ?? 0);
                $lineCost = round($usedQty * $unitCost, 4);
                $totalCost += $lineCost;

                BulkManufacturingItem::create([
                    'bulk_manufacturing_id' => $bulk->id,
                    'item_id' => $compId,
                    'quantity' => $usedQty,
                    'unit_cost' => $unitCost,
                    'total_cost' => $lineCost,
                ]);

                $comp->updateStockForStore($storeId, $after);

                StockAdjustment::create([
                    'item_id' => $compId,
                    'store_id' => $storeId,
                    'bulk_manufacturing_id' => $bulk->id,
                    'type' => 'decrease',
                    'quantity_change' => -$usedQty,
                    'quantity_before' => $before,
                    'quantity_after' => $after,
                    'reason' => 'Used in bulk manufacturing #' . $bulk->id,
                    'created_by' => Auth::id(),
                ]);
            }

            // Add bulk item to stock
            $beforeBulk = $bulkItem->getQuantityForStore($storeId);
            $afterBulk = $beforeBulk + $quantity;
            $bulkItem->updateStockForStore($storeId, $afterBulk);

            StockAdjustment::create([
                'item_id' => $bulkItem->id,
                'store_id' => $storeId,
                'bulk_manufacturing_id' => $bulk->id,
                'type' => 'increase',
                'quantity_change' => $quantity,
                'quantity_before' => $beforeBulk,
                'quantity_after' => $afterBulk,
                'reason' => 'Produced in bulk manufacturing #' . $bulk->id,
                'created_by' => Auth::id(),
            ]);

            // Update bulk item cost price
            $bulkItem->update([
                'cost_price' => $quantity > 0 ? $totalCost / $quantity : 0,
            ]);

            $this->processDivisions($bulk, $data['divisions'] ?? [], $bulkBomItemIds, $totalCost);

            if (($data['remaining_quantity'] ?? 0) <= 0.0001) {
                $bulk->remaining_quantity = 0;
                $bulk->waste_quantity = 0;
                $bulk->is_finished = true;
            } elseif ($data['is_finished'] && $data['remaining_quantity'] > 0) {
                $waste = $data['remaining_quantity'];
                $beforeBulk = $bulkItem->getQuantityForStore($storeId);
                if ($waste > $beforeBulk) {
                    throw ValidationException::withMessages([
                        'waste_quantity' => 'Insufficient bulk stock for waste deduction.',
                    ]);
                }
                $afterBulk = $beforeBulk - $waste;
                $bulkItem->updateStockForStore($storeId, $afterBulk);

                StockAdjustment::create([
                    'item_id' => $bulkItem->id,
                    'store_id' => $storeId,
                    'bulk_manufacturing_id' => $bulk->id,
                    'type' => 'decrease',
                    'quantity_change' => -$waste,
                    'quantity_before' => $beforeBulk,
                    'quantity_after' => $afterBulk,
                    'reason' => 'Waste from bulk manufacturing #' . $bulk->id,
                    'created_by' => Auth::id(),
                ]);

                $bulk->waste_quantity = $waste;
                $bulk->remaining_quantity = 0;
            } else {
                $bulk->remaining_quantity = $data['remaining_quantity'];
            }

            if (($data['remaining_quantity'] ?? 0) <= 0.0001) {
                $bulk->is_finished = true;
                $bulk->remaining_quantity = 0;
                $bulk->waste_quantity = 0;
            } else {
                $bulk->is_finished = $data['is_finished'];
            }
            $bulk->total_cost = $totalCost;
            $bulk->save();

            return $bulk;
        });
    }

    public function update(BulkManufacturing $bulk, array $data): BulkManufacturing
    {
        return DB::transaction(function () use ($bulk, $data) {
            $bulkItem = $bulk->item;
            $storeId = $bulk->store_id;
            $bulkBomItemIds = $bulk->items->pluck('item_id')->unique();
            $totalCost = (float) $bulk->total_cost;

            // Keep legacy records consistent: older bulks may have tracked remaining
            // quantity but missing stock in item_store.
            $this->syncBulkStockWithRemaining($bulk);

            if (isset($data['divisions']) && count($data['divisions']) > 0) {
                $this->processDivisions($bulk, $data['divisions'], $bulkBomItemIds, $totalCost);
            }

            $updateData = [
                'notes' => $data['notes'] ?? $bulk->notes,
                'remaining_quantity' => $data['remaining_quantity'],
                'is_finished' => $data['is_finished'] ?? $bulk->is_finished,
                'waste_quantity' => $bulk->waste_quantity,
                'total_cost' => $totalCost,
            ];

            if (($data['remaining_quantity'] ?? 0) <= 0.0001) {
                $updateData['remaining_quantity'] = 0;
                $updateData['is_finished'] = true;
                $updateData['waste_quantity'] = 0;
            } elseif ($updateData['is_finished'] && !$bulk->is_finished && $data['remaining_quantity'] > 0) {
                $waste = $data['remaining_quantity'];
                $beforeBulk = $bulkItem->getQuantityForStore($storeId);
                if ($waste > $beforeBulk) {
                    throw ValidationException::withMessages([
                        'waste_quantity' => 'Insufficient bulk stock for waste deduction.',
                    ]);
                }
                $afterBulk = $beforeBulk - $waste;
                $bulkItem->updateStockForStore($storeId, $afterBulk);

                StockAdjustment::create([
                    'item_id' => $bulkItem->id,
                    'store_id' => $storeId,
                    'bulk_manufacturing_id' => $bulk->id,
                    'type' => 'decrease',
                    'quantity_change' => -$waste,
                    'quantity_before' => $beforeBulk,
                    'quantity_after' => $afterBulk,
                    'reason' => 'Waste from bulk manufacturing #' . $bulk->id,
                    'created_by' => Auth::id(),
                ]);

                $updateData['waste_quantity'] = $waste;
                $updateData['remaining_quantity'] = 0;
            }

            $bulk->update($updateData);

            return $bulk;
        });
    }

    protected function processDivisions(BulkManufacturing $bulk, array $divisions, $bulkBomItemIds, &$totalCost): void
    {
        $bulkItem = $bulk->item;
        $storeId = $bulk->store_id;
        $availableBase = (float) $bulk->remaining_quantity;

        foreach ($divisions as $divData) {
            $targetId = $divData['target_item_id'];
            $target = Item::findOrFail($targetId);
            $targetBom = $target->billOfMaterial;

            if (!$targetBom) {
                throw ValidationException::withMessages([
                    'divisions' => "Target item {$target->name} has no Bill of Materials defined.",
                ]);
            }

            $produced = (float) $divData['quantity_produced'];
            $baseUsed = (float) $divData['total_base_used'];

            // Validate against this batch's tracked remaining quantity.
            if ($baseUsed > $availableBase) {
                throw ValidationException::withMessages([
                    'new_divisions' => "Insufficient bulk batch quantity for division: need {$baseUsed}, have {$availableBase}.",
                ]);
            }
            $availableBase -= $baseUsed;

            // Mirror division deduction to global item_store stock.
            $beforeBulk = $bulkItem->getQuantityForStore($storeId);
            $afterBulk = max(0, $beforeBulk - $baseUsed);
            $bulkItem->updateStockForStore($storeId, $afterBulk);

            StockAdjustment::create([
                'item_id' => $bulkItem->id,
                'store_id' => $storeId,
                'bulk_manufacturing_id' => $bulk->id,
                'type' => 'decrease',
                'quantity_change' => -$baseUsed,
                'quantity_before' => $beforeBulk,
                'quantity_after' => $afterBulk,
                'reason' => 'Used in division for ' . $target->name . ' in bulk manufacturing #' . $bulk->id,
                'created_by' => Auth::id(),
            ]);

            $division = BulkManufacturingDivision::create([
                'bulk_manufacturing_id' => $bulk->id,
                'target_item_id' => $targetId,
                'base_quantity_used' => $baseUsed,
                'quantity_produced' => $produced,
                'total_cost' => 0,
            ]);

            $divCost = 0;
            $targetBatchQty = $targetBom->batch_quantity ?? 1;
            $targetMultiplier = $produced / $targetBatchQty;

            $uniqueItems = $targetBom->items->filter(fn ($bomItem) => !$bulkBomItemIds->contains($bomItem->item_id));

            foreach ($uniqueItems as $bomItem) {
                $comp = $bomItem->component;
                if (!$comp) continue;

                $qtyPerBatch = (float) $bomItem->quantity;
                $usedQty = $qtyPerBatch * $targetMultiplier;

                $before = $comp->getQuantityForStore($storeId);
                if ($usedQty > $before) {
                    throw ValidationException::withMessages([
                        'divisions' => "Insufficient stock for {$comp->name} in division for {$target->name}: need {$usedQty}, have {$before}.",
                    ]);
                }

                $after = $before - $usedQty;
                $unitCost = (float) ($comp->cost_price ?? 0);
                $lineCost = round($usedQty * $unitCost, 4);
                $divCost += $lineCost;

                BulkManufacturingDivisionItem::create([
                    'bulk_man_division_id' => $division->id,
                    'item_id' => $comp->id,
                    'quantity' => $usedQty,
                    'unit_cost' => $unitCost,
                    'total_cost' => $lineCost,
                ]);

                $comp->updateStockForStore($storeId, $after);

                StockAdjustment::create([
                    'item_id' => $comp->id,
                    'store_id' => $storeId,
                    'bulk_manufacturing_id' => $bulk->id,
                    'type' => 'decrease',
                    'quantity_change' => -$usedQty,
                    'quantity_before' => $before,
                    'quantity_after' => $after,
                    'reason' => 'Used in bulk manufacturing #' . $bulk->id . ' division for ' . $target->name,
                    'created_by' => Auth::id(),
                ]);
            }

            // Add produced to target stock
            $beforeTarget = $target->getQuantityForStore($storeId);
            $afterTarget = $beforeTarget + $produced;
            $target->updateStockForStore($storeId, $afterTarget);

            StockAdjustment::create([
                'item_id' => $targetId,
                'store_id' => $storeId,
                'bulk_manufacturing_id' => $bulk->id,
                'type' => 'increase',
                'quantity_change' => $produced,
                'quantity_before' => $beforeTarget,
                'quantity_after' => $afterTarget,
                'reason' => 'Produced in bulk manufacturing #' . $bulk->id,
                'created_by' => Auth::id(),
            ]);

            $totalQuantity = $target->totalQuantity();
            $newStatus = $totalQuantity > 0 ? 'in_stock' : 'out_of_stock';

            if ($target->status !== $newStatus) {
                $target->update(['status' => $newStatus]);
            }

            $division->update(['total_cost' => $divCost]);
            $totalCost += $divCost;
        }
    }

    protected function syncBulkStockWithRemaining(BulkManufacturing $bulk): void
    {
        $bulkItem = $bulk->item;
        $storeId = $bulk->store_id;
        $remaining = (float) $bulk->remaining_quantity;
        $currentStock = $bulkItem->getQuantityForStore($storeId);

        if ($remaining <= 0 || $currentStock >= $remaining) {
            return;
        }

        $syncedStock = $remaining;
        $increaseBy = $syncedStock - $currentStock;
        $bulkItem->updateStockForStore($storeId, $syncedStock);

        StockAdjustment::create([
            'item_id' => $bulkItem->id,
            'store_id' => $storeId,
            'bulk_manufacturing_id' => $bulk->id,
            'type' => 'increase',
            'quantity_change' => $increaseBy,
            'quantity_before' => $currentStock,
            'quantity_after' => $syncedStock,
            'reason' => 'Auto-sync bulk stock from tracked remaining quantity for bulk manufacturing #' . $bulk->id,
            'created_by' => Auth::id(),
        ]);
    }
}
