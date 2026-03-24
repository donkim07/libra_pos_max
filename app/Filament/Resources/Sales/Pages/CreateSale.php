<?php

namespace App\Filament\Resources\Sales\Pages;

use Illuminate\Support\Facades\DB;
use App\Models\Sale;
use App\Models\StockAdjustment;
use App\Models\Item;
use App\Traits\UpdatesItemStatus;
use Illuminate\Support\Facades\Auth;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\Sales\SaleResource;
use Illuminate\Validation\ValidationException;

class CreateSale extends CreateRecord
{
    protected static string $resource = SaleResource::class;

    use UpdatesItemStatus;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $items = $data['items'] ?? [];

        // Calculate items total
        $itemsTotal = collect($items)
            ->sum(fn ($item) => (float) ($item['total'] ?? 0));

        $data['total'] = $itemsTotal;

        // Ensure paid_amount equals total (since it's auto-calculated)
        $data['paid_amount'] = $itemsTotal;

        // Determine payment status and sale status
        $paid = (float) ($data['paid_amount'] ?? 0);

        if ($paid >= $itemsTotal) {
            $data['payment_status'] = 'full_payment';
            $data['status'] = 'completed';
        } elseif ($paid > 0) {
            $data['payment_status'] = 'partial_payment';
            $data['status'] = 'pending';
        } else {
            $data['payment_status'] = 'unpaid';
            $data['status'] = 'pending';
        }

        // Optional: Set a default store_id on the Sale if your model requires it (e.g. for reporting)
        // Here we use the user's default store or the first item's store
        if (empty($data['store_id'])) {
            $data['store_id'] = Auth::user()?->store_id;
            if (empty($data['store_id']) && !empty($items)) {
                $data['store_id'] = $items[0]['store_id'] ?? null;
            }
        }

        // ────────────────────────────────────────────────
        // NEW: Cumulative validation across all repeater items
        // Prevent saving if total qty for any item+store combo exceeds available
        // ────────────────────────────────────────────────
        $grouped = [];
        foreach ($items as $itemData) {
            $itemId  = $itemData['item_id'] ?? null;
            $storeId = $itemData['store_id'] ?? null;
            $qty     = (float) ($itemData['quantity'] ?? 0);

            if ($itemId && $storeId && $qty > 0) {
                $key = $itemId . '-' . $storeId;
                $grouped[$key] = ($grouped[$key] ?? 0) + $qty;
            }
        }

        $errors = [];
        foreach ($grouped as $key => $totalRequested) {
            [$itemId, $storeId] = explode('-', $key);
            $item = Item::find($itemId);

            if ($item) {
                $available = $item->getQuantityForStore((int) $storeId);

                if ($totalRequested > $available) {
                    $storeName = \App\Models\Store::find($storeId)?->name ?? "Store ID {$storeId}";
                    $errors[] = "Total quantity for item '{$item->name}' in {$storeName} exceeds available stock ({$available}). Requested total: {$totalRequested}.";
                }
            }
        }

        if ($errors) {
            // throw ValidationException::withMessages([
            //     'items' => $errors, // Filament will display these as errors on the repeater
            // ]);
            throw ValidationException::withMessages([
    'items' => implode("\n", $errors), // or use a single string
]);
        }



        return $data;
    }

    protected function afterCreate(): void
    {
        DB::transaction(function () {
            $sale = $this->record;

            // Create sale items (now including store_id per item)
            foreach (($this->data['items'] ?? []) as $itemData) {
                $sale->saleitems()->create([
                    'item_id'   => $itemData['item_id'],
                    'store_id'  => $itemData['store_id'],
                    'quantity'  => $itemData['quantity'],
                    'price'     => $itemData['price'],
                    'discount'  => $itemData['discount'] ?? 0,
                    'total'     => $itemData['total'],
                ]);
            }

            // Handle inventory adjustments (per-store, with StockAdjustment records)
            $this->handleInventory($sale);

            $this->handleAccountBalance($sale);
        });
    }

    protected function handleInventory(Sale $sale): void
    {
        foreach ($sale->saleItems as $saleItem) {
            $item = $saleItem->item;
            $storeId = $saleItem->store_id;

            if (!$item || !$storeId) {
                continue;
            }

            // Fetch current quantity from item_store
            $quantityBefore = $item->getQuantityForStore($storeId);
            $quantityChange = $saleItem->quantity;
            $quantityAfter  = $quantityBefore - $quantityChange;

            // Safety net: If somehow negative (e.g. concurrent changes), prevent
            if ($quantityAfter < 0) {
                // You can throw an exception here if strict, or adjust/log
                // For now, we'll cap at 0 and note in adjustment
                $quantityAfter = 0;
            }

            // Update the item_store quantity
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


            // Create audit record in stock_adjustments
            StockAdjustment::create([
                'item_id'         => $item->id,
                'store_id'        => $storeId,
                'sale_id'         => $sale->id,
                'type'            => 'decrease',
                'quantity_change' => -$quantityChange,
                'quantity_before' => $quantityBefore,
                'quantity_after'  => $quantityAfter,
                'reason'          => 'Sale #' . $sale->id . ' (Item: ' . $item->name . ')',
                'created_by'      => Auth::id(),
            ]);

            $this->updateItemStatus($item);
        }
    }

    protected function handleAccountBalance(Sale $sale): void
    {
        if ($sale->account_id && $sale->paid_amount > 0) {
            $account = $sale->account;
            if ($account) {
                $newBalance = $account->balance + $sale->paid_amount;
                $account->update(['balance' => $newBalance]);
            }
        }
    }
}
