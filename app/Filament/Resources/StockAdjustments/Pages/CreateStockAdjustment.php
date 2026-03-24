<?php

namespace App\Filament\Resources\StockAdjustments\Pages;

use App\Filament\Resources\StockAdjustments\StockAdjustmentResource;
use App\Models\Item;
use App\Models\StockAdjustment;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class CreateStockAdjustment extends CreateRecord
{
    protected static string $resource = StockAdjustmentResource::class;

protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
{
    return DB::transaction(function () use ($data) {
        $createdBy = Auth::id();
        $adjustments = $data['adjustments'] ?? [];

        $hasValidAdjustment = false;
        $firstAdjustment = null;

        foreach ($adjustments as $index => $adj) {
            $storeId = $adj['store_id'] ?? null;

            if (!$storeId || empty($adj['item_id'])) {
                continue;
            }

            $item = Item::find($adj['item_id']);
            if (!$item) {
                continue;
            }

            $quantityBefore = (float) ($adj['quantity_before'] ?? $item->getQuantityForStore($storeId));
            $quantityAfter  = (float) ($adj['quantity_after'] ?? $quantityBefore);
            $quantityChange = $quantityAfter - $quantityBefore;

            if (abs($quantityChange) < 0.0001) {
                continue;
            }

            // Backend safety check (optional since frontend handles it)
            if ($quantityAfter < 0) {
                continue; // Skip this adjustment silently
            }

            $type = $quantityChange >= 0 ? 'increase' : 'decrease';

            $adjustment = StockAdjustment::create([
                'item_id'          => $item->id,
                'store_id'         => $storeId,
                'type'             => $type,
                'quantity_change'  => $quantityChange,
                'quantity_before'  => $quantityBefore,
                'quantity_after'   => $quantityAfter,
                'reason'           => trim($adj['reason'] ?? 'Bulk stock adjustment'),
                'created_by'       => $createdBy,
            ]);

            $item->updateStockForStore($storeId, $quantityAfter);



            // 🔄 Recalculate total stock AFTER update
            $totalQuantity = DB::table('item_store')
                ->where('item_id', $item->id)
                ->sum('quantity');

            // 🔁 Update item status if needed
            $newStatus = $totalQuantity > 0 ? 'in_stock' : 'out_of_stock';

            if ($item->status !== $newStatus) {
                $item->update(['status' => $newStatus]);
            }




            if (!$firstAdjustment) {
                $firstAdjustment = $adjustment;
            }

            $hasValidAdjustment = true;
        }

        if (!$hasValidAdjustment) {
            // This is the only error that might show
            throw ValidationException::withMessages([
                'adjustments' => 'No valid adjustments were made.',
            ]);
        }

        return $firstAdjustment;
    });
}

}
