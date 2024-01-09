<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PasswordResetRequest extends Model
{
    use HasFactory;

    protected $table = 'password_reset_requests';

    protected $fillable = [
        'token',
        'expires_at',
        'status',
        'user_id',
        'reset_token',
        'token_expiration',
        'name', // Added from new code
        'pwd', // Added new column 'pwd'
    ];

    protected $hidden = [
        'reset_token',
        'pwd', // Hide the 'pwd' column
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'expires_at' => 'datetime',
        'token_expiration' => 'datetime',
        'status' => 'string',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // ... existing methods ...
}
