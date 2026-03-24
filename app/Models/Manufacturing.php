<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Manufacturing extends Model
{
    protected $fillable = [
        'item_id',
        'quantity',
        'store_id',
        'total_cost',
        'notes',
        'date_manufactured',
        'created_by',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function store()
    {
        return $this->belongsTo(Store::class, 'store_id');
    }

    public function ingredients()
    {
        return $this->hasMany(ManufacturingItem::class);
    }

    public function stockAdjustments()
    {
        return $this->hasMany(StockAdjustment::class);
    }

    public function manufacturingItems()
{
    return $this->hasMany(ManufacturingItem::class);
}

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }




}

