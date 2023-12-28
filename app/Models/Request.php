<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Request extends Model
{
    use HasFactory;

    protected $table = 'requests';

    protected $fillable = [
        'area',
        'menu',
        'hair_concerns',
        'status',
        'user_id',
    ];

    protected $hidden = [
        // Existing hidden columns
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function requestImages()
    {
        return $this->hasMany(RequestImage::class, 'request_id');
    }

    // Define the relationship with RequestAreaSelections
    public function requestAreaSelections()
    {
        return $this->hasMany(RequestAreaSelection::class, 'request_id');
    }

    // Define the relationship with RequestMenuSelections
    public function requestMenuSelections()
    {
        return $this->hasMany(RequestMenuSelection::class, 'request_id');
    }
}
