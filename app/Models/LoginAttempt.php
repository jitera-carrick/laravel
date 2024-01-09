<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoginAttempt extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'login_attempts';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'attempted_at',
        'successful',
        'email', // Changed from 'ip_address' to 'email'
        'timestamp', // Changed from 'user_id' to 'timestamp'
        'created_at', // New column added to fillable
        'updated_at', // New column added to fillable
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'attempted_at' => 'datetime',
        'successful' => 'boolean',
        'email' => 'string', // Added 'email' to casts
        'timestamp' => 'datetime', // Added 'timestamp' to casts
    ];

    /**
     * Get the user that owns the login attempt.
     */
    public function user()
    {
        // Removed user relationship as it's no longer relevant with the new fields
    }
}
