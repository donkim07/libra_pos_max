<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PaymentMethod extends Model
{
    /** @use HasFactory<\Database\Factories\PaymentMethodFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
    ];

        /**
     * Get the payment methods's first and last name
     */
    public function getDisplayNameAttribute(): string
    {
        return Str::of($this->name)
            ->replace('_', ' ')
            ->title()
            ->toString();
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

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }


}
