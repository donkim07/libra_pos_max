<?php

namespace App\Models;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends Model
{
    /** @use HasFactory<\Database\Factories\CategoryFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
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

    public function items()
    {
        return $this->hasMany(Item::class);
    }

        public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

}
