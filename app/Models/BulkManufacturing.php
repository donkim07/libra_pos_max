<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BulkManufacturing extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id',
        'quantity',
        'remaining_quantity',
        'is_finished',
        'waste_quantity',
        'date_manufactured',
        'store_id',
        'notes',
        'total_cost',
        'created_by',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'total_cost' => 'decimal:4',
        'remaining_quantity' => 'decimal:4',
        'waste_quantity' => 'decimal:4',
        'is_finished' => 'boolean',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items()
    {
        return $this->hasMany(BulkManufacturingItem::class);
    }

    public function divisions()
    {
        return $this->hasMany(BulkManufacturingDivision::class);
    }
}
