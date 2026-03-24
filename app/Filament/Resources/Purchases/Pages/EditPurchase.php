<?php

namespace App\Filament\Resources\Purchases\Pages;

use App\Models\Item;
use App\Models\Purchase;
use App\Models\PurchasesItem;
use App\Models\StockAdjustment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\ValidationException;
use App\Filament\Resources\Purchases\PurchaseResource;

class EditPurchase extends EditRecord
{
    protected static string $resource = PurchaseResource::class;

    public function mount(int | string $record): void
    {
        parent::mount($record);
        $this->form->fill($this->getFormData());
    }

    protected function getFormData(): array
    {
        $data = $this->record->toArray();

        $purchaseItems = $this->record->items()->get()->map(function ($pItem) {
            $item = $pItem->item;
            return [
                'item_id'            => $pItem->item_id,
                'store_id'           => $pItem->store_id,
                'quantity'           => $pItem->quantity,
                'price'              => number_format($pItem->price ?? 0, 2, '.', ''),
                'total'              => number_format($pItem->total ?? 0, 2, '.', ''),
                'quantity_on_hand'   => $item ? $item->getQuantityForStore($pItem->store_id) : 0,
                'available_quantity' => $item ? $item->getQuantityForStore($pItem->store_id) : 0,
            ];
        })->toArray();

        $data['items'] = $purchaseItems;

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Similar to create: recalculate total, statuses, etc.
        $items = $data['items'] ?? [];
        $itemsTotal = collect($items)->sum(fn ($item) => (float) ($item['total'] ?? 0));

        $data['total'] = $itemsTotal;
        $data['paid_amount'] = (float) ($data['paid_amount'] ?? $itemsTotal);

        $paid = $data['paid_amount'];
        $data['status'] = 'completed';
        $data['payment_status'] = $paid >= $itemsTotal ? 'full_payment' : ($paid > 0 ? 'partial_payment' : 'unpaid');

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return DB::transaction(function () use ($record, $data) {
            $oldPaid = $record->paid_amount;
            $oldItems = $record->items()->get();

            $record->update($data);

            // Adjust account balance (reverse old payment, apply new)
            $paidDelta = $record->paid_amount - $oldPaid;
            if ($paidDelta != 0 && $record->account_id) {
                $account = $record->account;
                if ($account) {
                    $account->decrement('balance', $paidDelta); // since purchase decreases balance
                }
            }

            // Reverse old stock increases
            foreach ($oldItems as $oldItem) {
                $item = $oldItem->item;
                if (!$item) continue;

                $storeId = $oldItem->store_id;
                $qtyBefore = $item->getQuantityForStore($storeId);
                $qtyAfter = $qtyBefore - $oldItem->quantity;

                $item->updateStockForStore($storeId, max($qtyAfter, 0));

                StockAdjustment::create([
                    'item_id'         => $item->id,
                    'store_id'        => $storeId,
                    'purchase_id'     => $record->id,
                    'type'            => 'decrease',
                    'quantity_change' => -$oldItem->quantity,
                    'quantity_before' => $qtyBefore,
                    'quantity_after'  => $qtyAfter,
                    'reason'          => 'Purchase Edit Reversal #' . $record->id,
                    'created_by'      => Auth::id(),
                ]);
            }

            $record->items()->delete();

            // Add new items + increase stock
            foreach ($data['items'] ?? [] as $itemData) {
                $item = Item::find($itemData['item_id']);
                if (!$item) continue;

                $storeId = $itemData['store_id'];
                $qtyBefore = $item->getQuantityForStore($storeId);
                $qtyAdded = (float) $itemData['quantity'];
                $qtyAfter = $qtyBefore + $qtyAdded;

                $item->updateStockForStore($storeId, $qtyAfter);

                PurchasesItem::create([
                    'purchase_id' => $record->id,
                    'item_id'     => $item->id,
                    'store_id'    => $storeId,
                    'quantity'    => $qtyAdded,
                    'price'       => $itemData['price'],
                    'total'       => $itemData['total'],
                ]);

                StockAdjustment::create([
                    'item_id'         => $item->id,
                    'store_id'        => $storeId,
                    'purchase_id'     => $record->id,
                    'type'            => 'increase',
                    'quantity_change' => $qtyAdded,
                    'quantity_before' => $qtyBefore,
                    'quantity_after'  => $qtyAfter,
                    'reason'          => 'Purchase Edit #' . $record->id,
                    'created_by'      => Auth::id(),
                ]);
            }

            return $record;
        });
    }
}
