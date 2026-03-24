<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ManufacturingItem extends Model
{
    protected $fillable = [
        'manufacturing_id',
        'item_id',
        'quantity',
        'unit_cost',
        'total_cost',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function manufacturing()
    {
        return $this->belongsTo(Manufacturing::class, 'manufacturing_id');
    }

}

