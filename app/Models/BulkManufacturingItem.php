<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BulkManufacturingItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'bulk_manufacturing_id',
        'item_id',
        'quantity',
        'unit_cost',
        'total_cost',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'unit_cost' => 'decimal:4',
        'total_cost' => 'decimal:4',
    ];

    public function bulkManufacturing()
    {
        return $this->belongsTo(BulkManufacturing::class);
    }

    public function component()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }
}
