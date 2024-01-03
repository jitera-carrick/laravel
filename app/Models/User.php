<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'password_hash',
        'password_salt',
        'last_password_reset',
        'remember_token',
        'is_logged_in',
        'created_at',
        'updated_at',
        'username',
        'session_token', // New column added to fillable
        'session_expiration', // New column added to fillable
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'password_hash',
        'password_salt',
        'session_token', // New column added to hidden
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_password_reset' => 'datetime',
        'is_logged_in' => 'boolean',
        'created_at' => 'datetime', // New column added to casts
        'updated_at' => 'datetime', // New column added to casts
        'session_expiration' => 'datetime', // New column added to casts
    ];

    // Define the one-to-many relationship with LoginAttempt
    public function loginAttempts()
    {
        return $this->hasMany(LoginAttempt::class, 'user_id');
    }

    // Define the one-to-many relationship with Request
    public function requests()
    {
        return $this->hasMany(Request::class, 'user_id');
    }

    // Define the one-to-many relationship with PasswordResetToken
    public function passwordResetTokens() // Updated relationship method to plural as it's one-to-many
    {
        return $this->hasMany(PasswordResetToken::class, 'user_id');
    }

    // Define the one-to-many relationship with Session
    public function sessions()
    {
        return $this->hasMany(Session::class, 'user_id');
    }

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
}
