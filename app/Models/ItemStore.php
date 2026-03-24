<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemStore extends Model
{
    //

    protected $fillable = [
        'item_id',
        'store_id',
        'quantity',
        'created_at',
        'updated_at',
    ];


    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}
