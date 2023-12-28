<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail; // Commented out, can be removed if not needed
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
    protected $table = 'users'; // New property added for table name

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'password_hash', // New column added to fillable
        'password_salt', // New column added to fillable
        'last_password_reset', // New column added to fillable
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'password_hash', // New column added to hidden
        'password_salt', // New column added to hidden
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_password_reset' => 'datetime', // New column added to casts
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

    // Define the relationship with PasswordResetToken
    // The relationship type (one-to-many or one-to-one) should be determined by the business logic.
    // If a user can have multiple password reset tokens at the same time, use hasMany.
    // If a user can only have one password reset token at a time, use hasOne.
    // For this example, we'll assume that a user can only have one password reset token at a time.
    public function passwordResetToken() // Relationship name changed to singular form
    {
        return $this->hasOne(PasswordResetToken::class, 'user_id');
    }

    // If the business logic allows for multiple password reset tokens, you can also include this method.
    // Uncomment the following method if needed.
    /*
    public function passwordResetTokens()
    {
        return $this->hasMany(PasswordResetToken::class, 'user_id');
    }
    */
}
