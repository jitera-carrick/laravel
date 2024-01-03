<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StylistRequest extends Model
{
    use HasFactory;

    protected $table = 'stylist_requests';

    protected $fillable = [
        'status',
        'user_id',
        'details', // Added from new code
        'created_at', // Added from new code
        'updated_at', // Added from new code
        'request_id', // New column added
    ];

    protected $hidden = [
        // Usually, we don't hide any fields in the stylist_requests table, but if needed, add them here.
        // No changes needed here as there is no conflict.
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'status' => 'string', // Assuming status is a string type
        'details' => 'json', // Assuming details is a JSON type
        // No new casts needed for 'request_id' as it is likely an integer (foreign key)
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // New relationship with Request model
    public function request()
    {
        return $this->belongsTo(Request::class, 'request_id');
    }
}
