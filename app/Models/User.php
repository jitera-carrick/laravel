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
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'email_verified_at',
        'password',
        'remember_token',
        'session_token',
        'is_logged_in',
        'session_expiration',
        'user_type',
        'last_login_at', // New column added to fillable
        'is_active', // New column added to fillable
        'hair_stylist_request_id', // New column added to fillable
        'stylist_request_id', // New column added to fillable
        'request_id', // New column added to fillable
        'session_last_active', // New column added to fillable
    ];

    /**
     * Get the password resets for the user.
     */
    public function passwordResets()
    {
        return $this->hasMany(PasswordReset::class, 'user_id');
    }

    /**
     * Get the email verification tokens for the user.
     */
    public function emailVerificationTokens()
    {
        return $this->hasMany(EmailVerificationToken::class, 'user_id');
    }

    /**
     * Get the stylist requests for the user.
     */
    public function stylistRequests()
    {
        return $this->hasMany(StylistRequest::class, 'user_id');
    }

    /**
     * Get the comments for the user.
     */
    public function comments()
    {
        return $this->hasMany(Comment::class, 'user_id');
    }

    /**
     * Get the hair stylist requests for the user.
     */
    public function hairStylistRequests()
    {
        return $this->hasMany(HairStylistRequest::class, 'user_id');
    }

    // Other existing relationships...

    // New relationships can be added below as needed.
}
