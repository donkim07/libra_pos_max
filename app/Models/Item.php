<?php

namespace App\Models;

use App\Models\Sale;
use App\Models\Unit;
use App\Models\User;
use App\Models\Store;
use App\Models\Category;
use App\Models\Supplier;
use App\Models\PurchasesItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Item extends Model
{
    /** @use HasFactory<\Database\Factories\ItemFactory> */
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'category_id',
        'sku',
        'barcode',
        'unit_id',
        'other_cost',
        'cost_price',
        'selling_price',
        'discount',
        'status',
        'is_active',
        'store_id',
        'image',
        'item_type',
    ];

        protected $casts = [
        'cost_price' => 'decimal:4',
        'selling_price' => 'decimal:2',
        'quantity' => 'decimal:3',
        'is_active' => 'boolean',
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





        public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }


    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function stores()
{
    return $this->belongsToMany(Store::class, 'item_store')
                ->withPivot('quantity')
                ->withTimestamps();
}

public function getTotalStockAttribute()
{
    return $this->stores()->sum('item_store.quantity');
    // or: return $this->stores()->sum('pivot.quantity');  // if withPivot is set
}

    public function suppliers()
    {
        return $this->hasMany(Supplier::class);
    }

    public function purchaseItems()
    {
        return $this->hasMany(PurchasesItem::class);
    }


    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    protected static ?string $recordTitleAttribute = 'title';


    public function billOfMaterial()
    {
        return $this->hasOne(BillOfMaterial::class);
    }

    public function usedInBomItems()
    {
        return $this->hasMany(BillOfMaterialItem::class, 'item_id');
    }



    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

        public function manufacturings()
    {
        return $this->hasMany(Manufacturing::class);
    }

        public function manufacturingItems()
    {
        return $this->hasMany(ManufacturingItem::class);
    }

    public function stockAdjustments()
    {
        return $this->hasMany(StockAdjustment::class);
    }


    public function salesItems()
    {
        return $this->hasMany(SalesItem::class);
    }



    /**
 * Get stock quantity for a specific store.
 * Returns 0 if no entry (new item/store combo).
 */
public function getQuantityForStore(?int $storeId): float
{
    if (!$storeId) {
        return 0; // Or throw exception if store required
    }

    $pivot = $this->stores()->where('store_id', $storeId)->first();

    return $pivot ? (float) $pivot->pivot->quantity : 0;
}

/**
 * Update or create stock for a store.
 */
// public function updateStockForStore(int $storeId, float $newQuantity): void
// {
//     $this->stores()->syncWithoutDetaching([
//         $storeId => ['quantity' => $newQuantity]
//     ]);
// }

public function updateStockForStore(int $storeId, float $newQuantity): void
{
    // Use updateOrCreate to ensure the record exists in item_store
    // DB::table('item_store')
    //     ->updateOrInsert(
    //         [
    //             'item_id' => $this->id,
    //             'store_id' => $storeId
    //         ],
    //         [
    //             'quantity' => $newQuantity,
    //             'updated_at' => now()
    //         ]
    //     );

    // Alternative using Eloquent relationship:
    $this->stores()->syncWithoutDetaching([
        $storeId => ['quantity' => $newQuantity]
    ]);
}

public function totalQuantity(): float
{
    return (float) $this->stores()
        ->sum('item_store.quantity');
}




}
