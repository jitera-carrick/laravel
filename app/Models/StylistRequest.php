<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StylistRequest extends Model
{
    use HasFactory;

    protected $table = 'stylist_requests';

    protected $fillable = [
        'details',
        'status',
        'user_id',
        'created_at',
        'updated_at',
    ];

    protected $hidden = [
        // If there are any columns that should be hidden for arrays, add them here.
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'details' => 'array',
        'status' => 'string', // Keep the cast for 'status' column from the existing code
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Keep the inverse one-to-many relationship with User from the existing code
    public function stylistRequests()
    {
        return $this->hasMany(User::class, 'stylist_request_id');
    }

    // Other relationships can be added here as needed.
}
