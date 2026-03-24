<?php

namespace App\Models;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Sale extends Model
{
    /** @use HasFactory<\Database\Factories\SaleFactory> */
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'store_id',
        'total',
        'paid_amount',
        // 'discount',
        'status',
        'payment_status',
        'receipt_number',
        'payment_method_id',
        'account_id',
        'receipt_date',
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

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }
    public function account()
    {
        return $this->belongsTo(Account::class);
    }
    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function saleItems()
    {
        return $this->hasMany(SalesItem::class);
    }

        public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function paymentMethods()
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id');
    }

    public function accounts()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

}

