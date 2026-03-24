<?php

namespace App\Filament\Resources\StockMovements\Pages;

use App\Filament\Resources\StockMovements\StockMovementResource;
use App\Models\Item;
use App\Models\StockMovement;
use App\Models\StockAdjustment; // ← add this import
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class CreateStockMovement extends CreateRecord
{
    protected static string $resource = StockMovementResource::class;

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        return DB::transaction(function () use ($data) {
            $createdBy    = Auth::id();
            $movements    = $data['movements'] ?? [];
            $firstMovement = null;
            $hasValidMovement = false;

            foreach ($movements as $index => $move) {
                $itemId          = $move['item_id']          ?? null;
                $sourceStoreId   = $move['source_store_id']   ?? null;
                $destStoreId     = $move['destination_store_id'] ?? null;
                $quantityToSend  = (float) ($move['quantity_to_send'] ?? 0);

                if (!$itemId || !$sourceStoreId || !$destStoreId || $quantityToSend <= 0) {
                    continue;
                }

                if ($sourceStoreId === $destStoreId) {
                    continue;
                }

                $item = Item::find($itemId);
                if (!$item) continue;

                $sourceQtyBefore = $item->getQuantityForStore($sourceStoreId);
                if ($quantityToSend > $sourceQtyBefore) {
                    continue; // safety (frontend should already block)
                }

                $sourceQtyAfter  = $sourceQtyBefore - $quantityToSend;
                $destQtyBefore   = $item->getQuantityForStore($destStoreId);
                $destQtyAfter    = $destQtyBefore + $quantityToSend;

                // ────────────────────────────────────────────────
                // 1. Create the main StockMovement record
                // ────────────────────────────────────────────────
                $movement = StockMovement::create([
                    'item_id'             => $item->id,
                    'quantity'            => $quantityToSend,
                    'source_store_id'     => $sourceStoreId,
                    'destination_store_id'=> $destStoreId,
                    'reference_number'    => 'MOV-' . now()->format('YmdHis') . '-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT),
                    'created_by'          => $createdBy,
                ]);

                // ────────────────────────────────────────────────
                // 2. Create StockAdjustment - SOURCE (decrease)
                // ────────────────────────────────────────────────
                StockAdjustment::create([
                    'item_id'         => $item->id,
                    'store_id'        => $sourceStoreId,
                    'type'            => 'decrease',
                    'quantity_change' => -$quantityToSend,           // negative change
                    'quantity_before' => $sourceQtyBefore,
                    'quantity_after'  => $sourceQtyAfter,
                    'reason'          => 'stock_movement',            // ← key reason for filtering/reporting
                    'created_by'      => $createdBy,
                    // optional: 'reference_id' => $movement->id,     // if you add relation later
                    // 'notes'           => "Transferred to store #{$destStoreId}",
                ]);

                // ────────────────────────────────────────────────
                // 3. Create StockAdjustment - DESTINATION (increase)
                // ────────────────────────────────────────────────
                StockAdjustment::create([
                    'item_id'         => $item->id,
                    'store_id'        => $destStoreId,
                    'type'            => 'increase',
                    'quantity_change' => $quantityToSend,             // positive change
                    'quantity_before' => $destQtyBefore,
                    'quantity_after'  => $destQtyAfter,
                    'reason'          => 'stock_movement',            // same reason
                    'created_by'      => $createdBy,
                    // 'reference_id'    => $movement->id,
                    // 'notes'           => "Received from store #{$sourceStoreId}",
                ]);

                // ────────────────────────────────────────────────
                // 4. Actually update stock levels (item_store pivot / quantity field)
                // ────────────────────────────────────────────────
                $item->updateStockForStore($sourceStoreId, $sourceQtyAfter);
                $item->updateStockForStore($destStoreId, $destQtyAfter);

                if (!$firstMovement) {
                    $firstMovement = $movement;
                }

                $hasValidMovement = true;
            }

            if (!$hasValidMovement) {
                throw ValidationException::withMessages([
                    'movements' => 'No valid stock transfers were processed.',
                ]);
            }

            return $firstMovement;
        });
    }
}
