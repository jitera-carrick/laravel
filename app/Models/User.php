
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Hash;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    // ... existing code ...

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'username',
        'email_verified_at',
        'password',
        'password_hash',
        'password_salt',
        'last_password_reset',
        'remember_token',
        'session_token',
        'is_logged_in',
        'session_expiration',
        'user_type',
        'created_at',
        'updated_at',
        'stylist_request_id', // Added new column to the fillable array
    ];

    // ... existing relationships ...

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
        // Updated the relationship to reflect the correct foreign key
        return $this->hasMany(StylistRequest::class, 'stylist_request_id');
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

    // ... existing methods ...

    /**
     * Automatically hash the password when it is set.
     *
     * @param  string  $value
     * @return void
     */
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Hash::make($value);
    }

    /**
     * Log out the user by setting is_logged_in to false and clearing session_token.
     */
    public function logoutUser()
    {
        $this->is_logged_in = false;
        $this->session_token = null;
        $this->save();
    }

    // ... additional methods if any ...
}
