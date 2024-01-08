
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
        'name', // Added from new code
        'email',
        'email_verified_at',
        'password',
        'remember_token',
        'session_token',
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

    // Define the one-to-many relationship with stylist_requests
    public function stylistRequests()
    {
        return $this->hasMany(StylistRequest::class, 'user_id');
    }

    // Define the one-to-many relationship with comments
    public function comments()
    {
        return $this->hasMany(Comment::class, 'user_id');
    }

    // Define the one-to-many relationship with hair_stylist_requests
    public function hairStylistRequests()
    {
        return $this->hasMany(HairStylistRequest::class, 'user_id');
    }

    /**
     * Send the email verification notification.
     */
    public function sendVerificationEmail() 
    { 
        $this->notify(new \App\Notifications\VerifyEmail); 
    }

    // Other existing relationships...

    // New relationships can be added below as needed.
}
