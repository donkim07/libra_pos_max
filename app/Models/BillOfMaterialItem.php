<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BillOfMaterialItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'bill_of_material_id',
        'item_id',
        'quantity',
        'unit_id',
        'unit_cost',
        'total_cost',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'unit_cost' => 'decimal:4',
        'total_cost' => 'decimal:4',
    ];

    public function billOfMaterial()
    {
        return $this->belongsTo(BillOfMaterial::class);
    }

    public function component()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    protected static function booted()
    {
        static::saving(function ($item) {
            $component = $item->component;

            $item->unit_cost ??= $component?->cost_price ?? 0;
            $item->unit_id ??= $component?->unit_id;  // NEW: auto-set unit from component
            $item->total_cost = $item->unit_cost * $item->quantity;
        });

        static::saved(fn ($item) => $item->billOfMaterial->recalculateCosts());
        static::deleted(fn ($item) => $item->billOfMaterial->recalculateCosts());
    }
}
