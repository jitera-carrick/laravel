
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

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'remember_token',
        'email_verified',
        'session_token',
        'session_expiration',
        'created_at',
        'updated_at',
        // Add any new columns to the fillable array here
        // Other existing fillable attributes...
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        // Add other casts here if necessary
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

    /**
     * Get the sessions for the user.
     */
    public function sessions()
    {
        return $this->hasMany(Session::class, 'user_id');
    }

    /**
     * Get the email verification associated with the user.
     */
    public function emailVerification()
    {
        return $this->hasOne(EmailVerification::class, 'user_id');
    }

    // Other existing methods and relationships...

    // New relationships can be added below as needed.

    /**
     * Update the user's password.
     *
     * @param string $newPassword
     * @return bool
     */
    public function updatePassword($newPassword)
    {
        $this->password = Hash::make($newPassword);
        return $this->save();
    }
}
