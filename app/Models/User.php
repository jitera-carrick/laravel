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
        'last_login_at',
        'is_active',
        // 'stylist_request_id', // This column does not seem to be in the table schema provided
        // 'hair_stylist_request_id', // This column does not seem to be in the table schema provided
    ];

    // Existing relationships...

    // Define the one-to-many relationship with password_reset_tokens
    public function passwordResetTokens()
    {
        return $this->hasMany(PasswordResetToken::class, 'user_id');
    }

    // Define the one-to-many relationship with personal_access_tokens
    public function personalAccessTokens()
    {
        return $this->hasMany(PersonalAccessToken::class, 'user_id');
    }

    // Define the one-to-many relationship with login_attempts
    public function loginAttempts()
    {
        return $this->hasMany(LoginAttempt::class, 'user_id');
    }

    // Define the one-to-many relationship with email_verification_tokens
    public function emailVerificationTokens()
    {
        return $this->hasMany(EmailVerificationToken::class, 'user_id');
    }

    // Define the one-to-many relationship with hair_stylist_requests
    public function hairStylistRequests()
    {
        return $this->hasMany(HairStylistRequest::class, 'user_id');
    }

    // Define the one-to-many relationship with password_reset_requests
    public function passwordResetRequests()
    {
        return $this->hasMany(PasswordResetRequest::class, 'user_id');
    }

    // Define the one-to-many relationship with requests
    public function requests()
    {
        return $this->hasMany(Request::class, 'user_id');
    }

    // Define the one-to-many relationship with sessions
    public function sessions()
    {
        return $this->hasMany(Session::class, 'user_id');
    }

    // Define the one-to-many relationship with stylist_requests
    public function stylistRequests()
    {
        return $this->hasMany(StylistRequest::class, 'user_id');
    }

    // Define the one-to-many relationship with password_policies
    public function passwordPolicies()
    {
        return $this->hasMany(PasswordPolicy::class, 'user_id');
    }

    // Define the one-to-many relationship with comments
    public function comments()
    {
        return $this->hasMany(Comment::class, 'user_id');
    }

    // Define the one-to-many relationship with email_logs
    public function emailLogs()
    {
        return $this->hasMany(EmailLog::class, 'user_id');
    }

    // Other existing relationships...

    // New relationships can be added below as needed.

    /**
     * Check if a user with the given email or username exists.
     *
     * @param string $email
     * @param string $username
     * @return bool
     */
    public function emailOrUsernameExists($email, $username)
    {
        return self::where('email', $email)->orWhere('username', $username)->exists();
    }

    /**
     * Generate a unique email confirmation token for the user.
     *
     * @return EmailVerificationToken
     */
    public function generateEmailConfirmationToken()
    {
        $token = new EmailVerificationToken([
            'token' => Str::random(60),
            'expires_at' => now()->addHours(24),
        ]);

        $this->emailVerificationTokens()->save($token);

        return $token;
    }

    /**
     * Update the user's password hash and salt.
     *
     * @param string $password_hash
     * @param string $password_salt
     * @return void
     */
    public function updatePassword($password_hash, $password_salt)
    {
        $this->update(compact('password_hash', 'password_salt'));
    }

    /**
     * Update the user's session information.
     *
     * @param string $sessionToken
     * @param \DateTime $sessionExpiration
     * @param bool $isLoggedIn
     * @return void
     */
    public function updateSessionInfo($sessionToken, $sessionExpiration, $isLoggedIn)
    {
        $this->forceFill(['session_token' => $sessionToken, 'session_expiration' => $sessionExpiration, 'is_logged_in' => $isLoggedIn])->save();
    }

    // Other existing methods...
}
