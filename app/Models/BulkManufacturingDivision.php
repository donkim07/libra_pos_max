<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BulkManufacturingDivision extends Model
{
    use HasFactory;

    protected $fillable = [
        'bulk_manufacturing_id',
        'target_item_id',
        'base_quantity_used',
        'quantity_produced',
        'total_cost',
    ];

    protected $casts = [
        'base_quantity_used' => 'decimal:4',
        'quantity_produced' => 'decimal:4',
        'total_cost' => 'decimal:4',
    ];

    public function bulkManufacturing()
    {
        return $this->belongsTo(BulkManufacturing::class);
    }

    public function target()
    {
        return $this->belongsTo(Item::class, 'target_item_id');
    }

    public function items()
    {
        return $this->hasMany(BulkManufacturingDivisionItem::class);
    }
}
