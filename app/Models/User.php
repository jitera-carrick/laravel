<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    use HasFactory;

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
        'password_hash',
        'session_token', // Added from new code
        'session_expiration', // Added from new code
        'keep_session', // Added from new code
        'created_at', // Added from new code
        'updated_at', // Added from new code
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password_hash',
        'session_token', // Added from new code
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'session_expiration' => 'datetime', // Added from new code
        'keep_session' => 'boolean', // Added from new code
    ];

    /**
     * Generate a new session token for the user.
     *
     * @return string
     */
    public function generateSessionToken()
    {
        $this->session_token = \Str::random(60);
        $this->save();

        return $this->session_token;
    }

    /**
     * Set the session expiration based on the keep_session attribute.
     *
     * @return void
     */
    public function setSessionExpiration()
    {
        $this->session_expiration = $this->keep_session ? now()->addYear() : now()->addHours(2);
        $this->save();
    }

    /**
     * Get the hair stylist requests associated with the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function hairStylistRequests()
    {
        return $this->hasMany(HairStylistRequest::class, 'user_id');
    }

    /**
     * Get the password reset requests associated with the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function passwordResetRequests() // Added from new code
    {
        return $this->hasMany(PasswordResetRequest::class, 'user_id');
    }
}
