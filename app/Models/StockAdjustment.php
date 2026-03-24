<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockAdjustment extends Model
{
    protected $fillable = [
        'item_id',
        'store_id',
        'manufacturing_id',
        'sale_id',
        'type',
        'quantity_change',
        'quantity_before',
        'quantity_after',
        'reason',
        'created_by',
    ];


protected $casts = [
    'quantity_change'  => 'float',
    'quantity_before'  => 'float',
    'quantity_after'   => 'float',
];
    public function item()
    {
        return $this->belongsTo(Item::class);
    }

        public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}

