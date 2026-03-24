<?php

namespace App\Models;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SaleOrder extends Model
{
    //
        /** @use HasFactory<\Database\Factories\SaleOrderFactory> */
        use HasFactory;
     protected $fillable = [
        'customer_id',
        'store_id',
        'total',
        'paid_amount',
        'status',
        'payment_status',
        'receipt_number',
        'payment_method_id',
        'account_id',
        'order_date',
        'expected_delivery_date',
        'delivery_status',
    ];

    protected static function booted()
    {
        static::creating(function ($order) {
            $order->created_by ??= Auth::id();
            $order->updated_by ??= Auth::id();
        });
        static::updating(function ($order) {
            $order->updated_by ??= Auth::id();
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

    public function saleOrderItems()
    {
        return $this->hasMany(SaleOrderItem::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

}
