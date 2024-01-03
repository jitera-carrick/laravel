<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    // ... existing code ...

    // Add new columns to fillable
    protected $fillable = [
        // ... existing fillable attributes ...
        'display_name', // New column added to fillable
        'gender', // New column added to fillable
        'date_of_birth', // New column added to fillable
        'request_id', // New column added to fillable
        'session_expiration', // New column added to fillable
        'is_verified', // New column added to fillable
    ];

    // Add new columns to casts
    protected $casts = [
        // ... existing casts ...
        'date_of_birth' => 'date', // New column added to casts
        'session_expiration' => 'datetime', // New column added to casts
        'is_verified' => 'boolean', // New column added to casts
    ];

    // ... existing relationships ...

    // Define the one-to-many relationship with PasswordResetRequest
    public function passwordResetRequests()
    {
        return $this->hasMany(PasswordResetRequest::class, 'user_id');
    }

    // Define the one-to-many relationship with StylistRequest
    public function stylistRequests()
    {
        return $this->hasMany(StylistRequest::class, 'user_id');
    }

    // ... any additional new relationships ...
}
