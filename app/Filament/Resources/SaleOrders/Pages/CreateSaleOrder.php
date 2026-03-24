<?php

namespace App\Filament\Resources\SaleOrders\Pages;

use App\Models\Item;
use App\Models\Store;
use App\Models\SaleOrder;
use App\Models\StockAdjustment;
use App\Traits\UpdatesItemStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Validation\ValidationException;
use App\Filament\Resources\SaleOrders\SaleOrderResource;

class CreateSaleOrder extends CreateRecord
{
    protected static string $resource = SaleOrderResource::class;

    use UpdatesItemStatus;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $items = $data['items'] ?? [];

        // Calculate items total
        $itemsTotal = collect($items)
            ->sum(fn ($item) => (float) ($item['total'] ?? 0));

        $data['total'] = $itemsTotal;

        // Set payment_status based on paid_amount
        $paid = (float) ($data['paid_amount'] ?? 0);
        if ($paid >= $itemsTotal) {
            $data['payment_status'] = 'full_payment';
            $data['status'] = 'completed'; // If full payment on creation, mark completed?
        } elseif ($paid > 0) {
            $data['payment_status'] = 'partial_payment';
            $data['status'] = 'pending';
        } else {
            $data['payment_status'] = 'unpaid';
            $data['status'] = 'pending';
        }

        // No stock check/validation on creation, as stock is adjusted only on fulfillment

        // Set store_id if needed
        if (empty($data['store_id'])) {
            $data['store_id'] = Auth::user()?->store_id;
            if (empty($data['store_id']) && !empty($items)) {
                $data['store_id'] = $items[0]['store_id'] ?? null;
            }
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        DB::transaction(function () {
            $order = $this->record;

            // Create order items
            foreach (($this->data['items'] ?? []) as $itemData) {
                $order->saleOrderItems()->create([
                    'item_id'   => $itemData['item_id'],
                    'store_id'  => $itemData['store_id'],
                    'quantity'  => $itemData['quantity'],
                    'price'     => $itemData['price'],
                    'discount'  => $itemData['discount'] ?? 0,
                    'total'     => $itemData['total'],
                ]);
            }

            // If full payment on creation, fulfill immediately (adjust stock, etc.)
            if ($order->payment_status === 'full_payment') {
                $this->fulfillOrder($order);
            } else {
                // Otherwise, handle partial payment (update account balance for advance)
                $this->handleAccountBalance($order);
            }
        });
    }

    // Additional method for fulfilling the order (can be called from edit page or action)
    protected function fulfillOrder(SaleOrder $order): void
    {
        // Check if already delivered/completed
        if ($order->delivery_status !== 'pending' || $order->status === 'completed') {
            return;
        }

        // Validate stock now, before adjustment
        $grouped = [];
        foreach ($order->saleOrderItems as $orderItem) {
            $itemId = $orderItem->item_id;
            $storeId = $orderItem->store_id;
            $qty = (float) $orderItem->quantity;

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
                    $storeName = Store::find($storeId)?->name ?? "Store ID {$storeId}";
                    $errors[] = "Total quantity for item '{$item->name}' in {$storeName} exceeds available stock ({$available}). Requested total: {$totalRequested}.";
                }
            }
        }

        if ($errors) {
            throw ValidationException::withMessages([
                'items' => implode("\n", $errors),
            ]);
        }

        // Adjust inventory
        foreach ($order->saleOrderItems as $orderItem) {
            $item = $orderItem->item;
            $storeId = $orderItem->store_id;

            if (!$item || !$storeId) {
                continue;
            }

            $quantityBefore = $item->getQuantityForStore($storeId);
            $quantityChange = $orderItem->quantity;
            $quantityAfter = $quantityBefore - $quantityChange;

            if ($quantityAfter < 0) {
                $quantityAfter = 0;
            }

            $item->updateStockForStore($storeId, $quantityAfter);

            $totalQuantity = DB::table('item_store')
                ->where('item_id', $item->id)
                ->sum('quantity');

            $newStatus = $totalQuantity > 0 ? 'in_stock' : 'out_of_stock';

            if ($item->status !== $newStatus) {
                $item->update(['status' => $newStatus]);
            }

            StockAdjustment::create([
                'item_id'         => $item->id,
                'store_id'        => $storeId,
                // 'sale_order_id'   => $order->id, // Add sale_order_id to StockAdjustment if needed (migration required)
                'type'            => 'decrease',
                'quantity_change' => -$quantityChange,
                'quantity_before' => $quantityBefore,
                'quantity_after'  => $quantityAfter,
                'reason'          => 'Sale Order #' . $order->id . ' (Item: ' . $item->name . ')',
                'created_by'      => Auth::id(),
            ]);

            $this->updateItemStatus($item);
        }

        // Update order status
        $order->update([
            'delivery_status' => 'delivered',
            'status' => 'completed',
        ]);

        // Handle remaining balance if partial
        $this->handleAccountBalance($order);
    }

    protected function handleAccountBalance(SaleOrder $order): void
    {
        if ($order->account_id && $order->paid_amount > 0) {
            $account = $order->account;
            if ($account) {
                $newBalance = $account->balance + $order->paid_amount;
                $account->update(['balance' => $newBalance]);
            }
        }
    }
}
