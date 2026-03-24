<?php

namespace App\Models;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Customer extends Model
{
    /** @use HasFactory<\Database\Factories\CustomerFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
    ];

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function payment_method()
    {
        return $this->hasMany(PaymentMethod::class);
    }

    public function accounts()
    {
        return $this->hasMany(Account::class);
    }

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


    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }


}
