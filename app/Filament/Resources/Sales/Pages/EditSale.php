<?php

namespace App\Filament\Resources\Sales\Pages;

use App\Filament\Resources\Sales\SaleResource;
use App\Models\Sale;
use App\Models\StockAdjustment;
use App\Models\Item;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\ValidationException;

class EditSale extends EditRecord
{
    protected static string $resource = SaleResource::class;

    public function mount(int | string $record): void
    {
        parent::mount($record);

        // Force fill the form with record + related items
        $this->form->fill($this->getFormData());
    }

    protected function getFormData(): array
    {
        // Start with the main sale record data
        $data = $this->record->toArray();

        // Load and map existing sale items to repeater format
        $saleItems = $this->record->saleItems()  // ← IMPORTANT: check this relation name
            ->get()
            ->map(function ($saleItem) {
                $item = $saleItem->item;

                return [
                    'item_id'            => $saleItem->item_id,
                    'store_id'           => $saleItem->store_id,
                    'quantity'           => $saleItem->quantity,
                    'price'              => number_format($saleItem->price ?? 0, 2, '.', ''),
                    'discount'           => $saleItem->discount ?? 0,
                    'total'              => number_format($saleItem->total ?? 0, 2, '.', ''),

                    // Pre-fill live fields so quantity_on_hand shows correctly on load
                    'quantity_on_hand'   => $item ? $item->getQuantityForStore($saleItem->store_id) : 0,
                    'available_quantity' => $item ? $item->getQuantityForStore($saleItem->store_id) : 0,
                ];
            })
            ->toArray();

        $data['items'] = $saleItems ?: [];  // Ensure it's an array even if empty

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Your existing mutate logic (total, paid_amount, status, cumulative validation)
        $items = $data['items'] ?? [];

        $itemsTotal = collect($items)->sum(fn ($item) => (float) ($item['total'] ?? 0));

        $data['total']       = $itemsTotal;
        $data['paid_amount'] = $itemsTotal;

        $paid = (float) $data['paid_amount'];

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

        // Cumulative stock check (same as before)
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
                $available = $item->getQuantityForStore((int)$storeId);
                if ($totalRequested > $available) {
                    $storeName = \App\Models\Store::find($storeId)?->name ?? "Store ID {$storeId}";
                    $errors[] = "Total qty for '{$item->name}' in {$storeName} exceeds stock ({$available}). Requested: {$totalRequested}.";
                }
            }
        }

        if ($errors) {
            throw ValidationException::withMessages([
                'items' => implode("\n", $errors),
            ]);
        }

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return DB::transaction(function () use ($record, $data) {
            $oldPaid = $record->paid_amount;

            $record->update($data);  // Simpler than fill+save

            // Account balance delta
            $delta = $record->paid_amount - $oldPaid;
            if ($delta != 0 && $record->account_id) {
                $account = $record->account;
                if ($account) {
                    $account->increment('balance', $delta);
                }
            }

            // Reverse old inventory
            foreach ($record->saleItems as $oldItem) {  // Note: after update, relation may need refresh if needed
                $item = $oldItem->item;
                if (!$item) continue;

                $storeId = $oldItem->store_id;
                $qtyBefore = $item->getQuantityForStore($storeId);
                $qtyAfter  = $qtyBefore + $oldItem->quantity;

                $item->updateStockForStore($storeId, $qtyAfter);

                StockAdjustment::create([
                    'item_id'         => $item->id,
                    'store_id'        => $storeId,
                    'sale_id'         => $record->id,
                    'type'            => 'increase',
                    'quantity_change' => $oldItem->quantity,
                    'quantity_before' => $qtyBefore,
                    'quantity_after'  => $qtyAfter,
                    'reason'          => 'Sale Edit Reversal #' . $record->id,
                    'created_by'      => Auth::id(),
                ]);
            }

            // Delete old items
            $record->saleItems()->delete();

            // Add new items + deduct stock
            foreach ($data['items'] ?? [] as $itemData) {
                $saleItem = $record->saleitems()->create([
                    'item_id'   => $itemData['item_id'],
                    'store_id'  => $itemData['store_id'],
                    'quantity'  => $itemData['quantity'],
                    'price'     => $itemData['price'],
                    'discount'  => $itemData['discount'] ?? 0,
                    'total'     => $itemData['total'],
                ]);

                $item = $saleItem->item;
                if (!$item) continue;

                $storeId = $saleItem->store_id;
                $qtyBefore = $item->getQuantityForStore($storeId);
                $qtyAfter  = $qtyBefore - $saleItem->quantity;

                if ($qtyAfter < 0) $qtyAfter = 0;

                $item->updateStockForStore($storeId, $qtyAfter);

                StockAdjustment::create([
                    'item_id'         => $item->id,
                    'store_id'        => $storeId,
                    'sale_id'         => $record->id,
                    'type'            => 'decrease',
                    'quantity_change' => -$saleItem->quantity,
                    'quantity_before' => $qtyBefore,
                    'quantity_after'  => $qtyAfter,
                    'reason'          => 'Sale Edit #' . $record->id,
                    'created_by'      => Auth::id(),
                ]);
            }

            return $record;
        });
    }
}
