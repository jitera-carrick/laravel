<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Str;

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
        'username',
        'password',
        'password_hash',
        'password_salt',
        'last_password_reset',
        'remember_token',
        'session_token',
        'is_logged_in',
        'session_expiration',
        'created_at',
        'updated_at',
        'user_type',
        // Add new fillable properties if any new columns are added to the users table
    ];

    // Define the one-to-many relationship with PasswordResetTokens
    public function passwordResetTokens()
    {
        return $this->hasMany(PasswordResetToken::class, 'user_id');
    }

    // Define the one-to-many relationship with LoginAttempts
    public function loginAttempts()
    {
        return $this->hasMany(LoginAttempt::class, 'user_id');
    }

    // Define the one-to-many relationship with PasswordPolicies
    public function passwordPolicies()
    {
        return $this->hasMany(PasswordPolicy::class, 'user_id');
    }

    // Define the one-to-many relationship with Requests
    public function requests()
    {
        return $this->hasMany(Request::class, 'user_id');
    }

    // Define the one-to-many relationship with StylistRequests
    public function stylistRequests()
    {
        return $this->hasMany(StylistRequest::class, 'user_id');
    }

    // Define the one-to-many relationship with Sessions
    public function sessions()
    {
        return $this->hasMany(Session::class, 'user_id');
    }

    // Define the one-to-many relationship with EmailLogs
    public function emailLogs()
    {
        return $this->hasMany(EmailLog::class, 'user_id');
    }

    // Define the one-to-many relationship with PasswordResetRequests
    public function passwordResetRequests()
    {
        return $this->hasMany(PasswordResetRequest::class, 'user_id');
    }

    // Define the one-to-many relationship with PersonalAccessTokens
    public function personalAccessTokens()
    {
        return $this->hasMany(PersonalAccessToken::class, 'user_id');
    }

    // Define the one-to-many relationship with Comments
    public function comments()
    {
        return $this->hasMany(Comment::class, 'user_id');
    }

    /**
     * Generate a verification token and assign it to the remember_token attribute.
     *
     * @return void
     */
    public function generateVerificationToken()
    {
        $this->remember_token = Str::random(60);
    }

    /**
     * Update the login status of the user.
     *
     * @param string $sessionToken
     * @return void
     */
    public function updateLoginStatus($sessionToken)
    {
        $this->is_logged_in = true;
        $this->session_token = $sessionToken;
        $this->save();
    }
}
