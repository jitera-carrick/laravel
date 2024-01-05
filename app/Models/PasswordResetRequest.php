<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PasswordResetRequest extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'password_reset_requests';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'status',
        'user_id',
        'request_time', // Added from new code
        'reset_token', // Retained from existing code
        'expiration', // Retained from existing code
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'reset_token', // Retained from existing code as it's sensitive data
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'request_time' => 'datetime', // Added from new code, assuming request_time is a datetime column
        'expiration' => 'datetime', // Retained from existing code
        'status' => 'string', // Retained from existing code, assuming status is a string type
    ];

    /**
     * Get the user that owns the password reset request.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // ... existing methods ...
}
