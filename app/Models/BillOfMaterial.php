<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BillOfMaterial extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id',
        'batch_quantity',
        'total_cost',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'batch_quantity' => 'decimal:3',
        'total_cost' => 'decimal:4',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function items()
    {
        return $this->hasMany(BillOfMaterialItem::class);
    }

    public function recalculateCosts(): void
    {
        $totalComponentCost = $this->items->sum('total_cost');

        $this->update([
            'total_cost' => $totalComponentCost,
        ]);

        // FIXED: other_cost comes from the assembly Item, not BOM
        $otherCost = (float) ($this->item->other_cost ?? 0);

        // Update assembly item's cost_price
        $this->item->update([
            'cost_price' => $this->batch_quantity > 0
                ? ($totalComponentCost / $this->batch_quantity) + $otherCost
                : 0,
        ]);
    }

    public function billOfMaterialItems()
    {
        return $this->hasMany(BillOfMaterialItem::class);
    }

    /* =======================
        COST CALCULATION
    ======================= */

        public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
