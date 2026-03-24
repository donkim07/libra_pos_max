<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransferFunds extends Model
{
    //
        protected $fillable = [
        'from_account_id',
        'to_account_id',
        'amount',
        'note',
        'created_by',
    ];

    public function fromAccount()
    {
        return $this->belongsTo(Account::class, 'from_account_id');
    }

    public function toAccount()
    {
        return $this->belongsTo(Account::class, 'to_account_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

}
