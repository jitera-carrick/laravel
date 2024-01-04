<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StylistRequest extends Model
{
    use HasFactory;

    protected $table = 'stylist_requests';

    protected $fillable = [
        'request_time', // Added from new code
        'status',
        'user_id',
        'created_at', // Kept from existing code
        'updated_at', // Kept from existing code
    ];

    protected $hidden = [
        // If there are any columns that should be hidden for arrays, add them here.
        // This line is from the new code and has been kept as a placeholder for future use.
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'request_time' => 'datetime', // Added from new code, assuming request_time is a datetime column
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function createStylistRequest($user_id, $request_time)
    {
        return self::create([
            'user_id' => $user_id,
            'request_time' => $request_time,
            'status' => 'pending',
        ]);
    }

    // If there are any other relationships that need to be defined, add them here.
    // This comment is from the new code and has been kept as a placeholder for future use.
}
