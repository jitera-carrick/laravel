
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StylistRequest extends Model
{
    use HasFactory;

    protected $table = 'stylist_requests';

    protected $fillable = [
        'details', // Updated to include 'details' column
        'status',
        'user_id',
        'created_at',
        'updated_at',
        // 'details' and 'status' are already included in the $fillable property
    ];

    protected $hidden = [
        // If there are any columns that should be hidden for arrays, add them here.
    ];

    protected $casts = [
        // 'details' => 'array' cast is already present to handle JSON encoding/decoding
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'details' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Other relationships can be added here as needed.
}
