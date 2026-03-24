<?php

namespace App\Models;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SalesItem extends Model
{
    /** @use HasFactory<\Database\Factories\SalesItemFactory> */
    use HasFactory;

    protected $fillable = [
        'sale_id',
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
            // $item->store_id ??= Auth::user()?->store_id;
        });
        static::updating(function ($item) {
            $item->updated_by ??= Auth::id();
        });
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class, 'sale_id');
    }
    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }


}
