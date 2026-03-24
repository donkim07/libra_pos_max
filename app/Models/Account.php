<?php

namespace App\Models;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Account extends Model
{
    /** @use HasFactory<\Database\Factories\AccountFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'balance',
        'status',
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

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function outgoingTransfers()
{
    return $this->hasMany(TransferFunds::class, 'from_account_id');
}

public function incomingTransfers()
{
    return $this->hasMany(TransferFunds::class, 'to_account_id');
}

}
