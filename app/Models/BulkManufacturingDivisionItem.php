<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BulkManufacturingDivisionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'bulk_man_division_id',
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

    public function bulkManufacturingDivision()
    {
        return $this->belongsTo(BulkManufacturingDivision::class);
    }

    public function component()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }
}
