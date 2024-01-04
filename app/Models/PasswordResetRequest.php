<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PasswordResetRequest extends Model
{
    use HasFactory;

    protected $table = 'password_reset_requests';

    protected $fillable = [
        'request_time', // Added from new code
        'reset_token',
        'status',
        'user_id',
        'expiration', // Retained from existing code
    ];

    protected $hidden = [
        'reset_token', // Retained from existing code as it's sensitive data
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'request_time' => 'datetime', // Added from new code, assuming request_time is a datetime column
        'expiration' => 'datetime', // Retained from existing code
        'status' => 'string', // Retained from existing code, assuming status is a string type
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // If there are any additional methods or relationships from the new code, they should be added here.
    // No additional methods or relationships were provided in the new code snippet.
}
