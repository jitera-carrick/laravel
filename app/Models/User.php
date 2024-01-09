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
        'email',
        'password',
        'remember_token',
        'email_verified_at',
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
     * Generate a unique verification token for email verification.
     *
     * @return string
     */
    public function generateVerificationToken()
    {
        do {
            $token = \Illuminate\Support\Str::random(60);
        } while (self::where('remember_token', $token)->exists());

        return $token;
    }

    // Other existing methods and relationships...

    /**
     * Update the user's password.
     *
     * @param string $password
     * @return bool
     */
    public function updatePassword($password)
    {
        $this->password = \Illuminate\Support\Facades\Hash::make($password);
        return $this->save();
    }

    /**
     * Get the password resets for the user.
     */
    public function passwordResets()
    {
        return $this->hasMany(PasswordReset::class, 'user_id');
    }

    // Other existing methods and relationships...

    // New relationships can be added below as needed.
}
