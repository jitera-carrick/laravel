<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailVerificationToken extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'token',
        'expires_at',
        'used', // Include 'used' attribute in the fillable array as per the existing code requirements
        'user_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'used' => 'boolean', // Cast 'used' attribute to 'boolean' as per the existing code requirements
        'expires_at' => 'datetime',
    ];

    /**
     * Generate a unique token, set the 'expires_at' field, and associate it with the user.
     *
     * @param User $user
     * @return EmailVerificationToken
     */
    public static function generateFor(User $user)
    {
        do {
            $token = new self;
            $token->token = bin2hex(openssl_random_pseudo_bytes(16));
            $token->expires_at = now()->addHours(24);
            $token->used = false; // Initialize 'used' attribute as false when generating a new token
            $token->user()->associate($user);
            $token->save();
        } while (self::where('token', $token->token)->exists());

        return $token;
    }

    /**
     * Check if the token is valid and has not expired.
     *
     * @return bool
     */
    public function isValid()
    {
        return !$this->used && $this->expires_at->isFuture(); // Include 'used' check as per the existing code requirements
    }

    /**
     * The user that the verification token belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
