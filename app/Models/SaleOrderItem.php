<?php

namespace App\Models;

use App\Models\Item;
use App\Models\SaleOrder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SaleOrderItem extends Model
{
    //
    /** @use HasFactory<\Database\Factories\SaleOrderItemFactory> */
    use HasFactory;

    protected $fillable = [
        'sale_order_id',
        'item_id',
        'store_id',
        'quantity',
        'price',
        'discount',
        'total',
    ];

    protected static function booted()
    {
        static::creating(function ($item) {
            $item->created_by ??= Auth::id();
            $item->updated_by ??= Auth::id();
        });
        static::updating(function ($item) {
            $item->updated_by ??= Auth::id();
        });
    }

    public function saleOrder()
    {
        return $this->belongsTo(SaleOrder::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
