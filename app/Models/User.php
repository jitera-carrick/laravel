<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
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
        'password',
        'password_hash', // Added from new code
        'session_token', // Added from new code
        'session_expiration', // Added from new code
        'role', // Added from new code
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'password_hash', // Added from new code, assuming we want to hide the password hash as well
        'session_token', // Added from new code, assuming we want to hide the session token as well
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'session_expiration' => 'datetime', // Added from new code, assuming session_expiration is a datetime
    ];

    // Relationships

    /**
     * Get the password reset tokens associated with the user.
     */
    public function passwordResetTokens()
    {
        return $this->hasMany(PasswordResetToken::class, 'user_id');
    }

    /**
     * Get the sessions associated with the user.
     */
    public function sessions()
    {
        return $this->hasMany(Session::class, 'user_id');
    }

    // Existing relationships

    /**
     * Get the password reset token associated with the user.
     */
    public function passwordResetToken()
    {
        // The new code does not change this method, so it remains as it is in the existing code.
        return $this->hasOne(PasswordResetToken::class);
    }

    /**
     * Get the password resets associated with the user.
     */
    public function passwordResets()
    {
        // This method is added from the new code.
        return $this->hasMany(PasswordReset::class, 'user_id');
    }

    /**
     * Get the stylist requests associated with the user.
     */
    public function stylistRequests()
    {
        // This method is added from the new code.
        return $this->hasMany(StylistRequest::class, 'user_id');
    }
}
