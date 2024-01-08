<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Request;

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
        // Add new column names to the $fillable array if needed
    ];

    protected $hidden = [
        // If there are any columns that should be hidden for arrays, add them here.
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'details' => 'array',
        // Add new date/time columns to the $casts array if needed
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function requests()
    {
        return $this->hasMany(Request::class, 'stylist_request_id');
    }

    // Other relationships can be added here as needed.
}
