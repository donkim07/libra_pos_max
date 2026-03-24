<?php

namespace App\Traits;

use App\Models\Item;

trait UpdatesItemStatus
{
    protected function updateItemStatus(Item $item): void
    {
        $total = $item->getTotalStockAttribute(); // sums pivot table

        $newStatus = $total > 0 ? 'in_stock' : 'out_of_stock';

        if ($item->status !== $newStatus) {
            $item->updateQuietly(['status' => $newStatus]);
            // updateQuietly = no events, no timestamps change
        }
    }
}
