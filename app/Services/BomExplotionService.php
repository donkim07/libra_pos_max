<?php

namespace App\Services;

use App\Models\Item;
use Illuminate\Support\Collection;

class BomExplotionService
{
    /**
     * Explode an item into its raw components
     *
     * @param Item $item
     * @param float $quantity
     * @return Collection
     */
    public function explode(Item $item, float $quantity = 1): Collection
    {
        $results = collect();

        $this->resolve($item, $quantity, $results);

        return $results
            ->groupBy('item_id')
            ->map(function ($rows) {
                return [
                    'item' => $rows->first()['item'],
                    'quantity' => $rows->sum('quantity'),
                    'unit_cost' => $rows->first()['unit_cost'],
                    'total_cost' => $rows->sum('total_cost'),
                ];
            });
    }

    /**
     * Recursive resolver
     */
    protected function resolve(Item $item, float $requiredQty, Collection &$results): void
    {
        // If item has no BOM → RAW MATERIAL
        if (!$item->billOfMaterial) {
            $results->push([
                'item_id' => $item->id,
                'item' => $item,
                'quantity' => $requiredQty,
                'unit_cost' => $item->cost_price,
                'total_cost' => $requiredQty * $item->cost_price,
            ]);
            return;
        }

        $bom = $item->billOfMaterial;

        $multiplier = $requiredQty / $bom->batch_quantity;

        foreach ($bom->items as $bomItem) {
            $component = $bomItem->component;

            $this->resolve(
                $component,
                $bomItem->quantity * $multiplier,
                $results
            );
        }
    }
}
