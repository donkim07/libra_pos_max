<?php

namespace App\Filament\Resources\Purchases\Pages;

use App\Filament\Resources\Purchases\PurchaseResource;
use App\Models\Purchase;
use App\Models\PurchasesItem;
use App\Models\StockAdjustment;
use App\Models\Item;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Validation\ValidationException;

class CreatePurchase extends CreateRecord
{
    protected static string $resource = PurchaseResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $items = $data['items'] ?? [];

        $itemsTotal = collect($items)->sum(fn ($item) => (float) ($item['total'] ?? 0));
        $data['total'] = $itemsTotal;

        // paid_amount defaults to total, but allow partial
        $data['paid_amount'] = (float) ($data['paid_amount'] ?? $itemsTotal);

        $paid = $data['paid_amount'];
        $data['status'] = 'completed'; // or 'pending' if partial
        $data['payment_status'] = $paid >= $itemsTotal ? 'full_payment' : ($paid > 0 ? 'partial_payment' : 'unpaid');

        if (empty($data['store_id']) && !empty($items)) {
            $data['store_id'] = $items[0]['store_id'] ?? Auth::user()?->store_id;
        }

        return $data;
    }

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        return DB::transaction(function () use ($data) {
            $purchase = Purchase::create([
                'supplier_id'        => $data['supplier_id'],
                'total'              => $data['total'],
                'paid_amount'        => $data['paid_amount'],
                'discount'           => $data['discount'] ?? 0,
                'status'             => $data['status'],
                'payment_status'     => $data['payment_status'],
                'payment_method_id'  => $data['payment_method_id'],
                'account_id'         => $data['account_id'],
                'purchase_date'      => $data['purchase_date'] ?? now(),
                'reference_number'   => $data['reference_number'],
                'notes'              => $data['notes'] ?? null,
                'created_by'         => Auth::id(),
            ]);

            // Decrease account balance
            if ($purchase->account_id && $purchase->paid_amount > 0) {
                $account = $purchase->account;
                if ($account && $account->balance >= $purchase->paid_amount) {
                    $account->decrement('balance', $purchase->paid_amount);
                } else {
                    throw ValidationException::withMessages([
                        'account_id' => 'Insufficient balance in selected account.',
                    ]);
                }
            }

            // Add items & increase stock
            foreach ($data['items'] ?? [] as $itemData) {
                $item = Item::find($itemData['item_id']);
                if (!$item) continue;

                $storeId = $itemData['store_id'];
                $qtyBefore = $item->getQuantityForStore($storeId);
                $qtyAdded = (float) $itemData['quantity'];
                $qtyAfter = $qtyBefore + $qtyAdded;

                $item->updateStockForStore($storeId, $qtyAfter);

                PurchasesItem::create([
                    'purchase_id' => $purchase->id,
                    'item_id'     => $item->id,
                    'store_id'    => $storeId,
                    'quantity'    => $qtyAdded,
                    'price'       => $itemData['price'],
                    'total'       => $itemData['total'],
                ]);

                StockAdjustment::create([
                    'item_id'         => $item->id,
                    'store_id'        => $storeId,
                    'purchase_id'     => $purchase->id,
                    'type'            => 'increase',
                    'quantity_change' => $qtyAdded,
                    'quantity_before' => $qtyBefore,
                    'quantity_after'  => $qtyAfter,
                    'reason'          => 'Purchase #' . $purchase->id,
                    'created_by'      => Auth::id(),
                ]);
            }

            return $purchase;
        });
    }
}
