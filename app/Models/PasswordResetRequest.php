<?php

namespace App\Models; // No change

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
        'token',
        'expires_at',
        'status',
        'user_id', // Ensure 'user_id' is fillable
        'user_id',
        'reset_token',
        'token_expiration',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'reset_token',
        // 'reset_token' should be hidden for serialization
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        // Ensure 'expires_at' and 'token_expiration' are cast to 'datetime'
        'expires_at' => 'datetime',
        'token_expiration' => 'datetime',
        'status' => 'string',
    ];

    /**
     * Get the user that owns the password reset request.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
        // Define the inverse one-to-many relationship with User
    }

    // ... existing methods ...
}
