<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shop extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address', // New attribute added to fillable
    ];

    protected $attributes = [
        // Existing default attributes (if any)
    ];

    protected static function boot()
    {
        parent::boot();

        static::updating(function ($shop) {
            $shop->updated_at = now();
        });
    }

    // Existing relationships and methods (if any)
}
