<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str; // Use Str::random from existing code

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
        'verified',
        'used', // Added from new code
        'user_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'expires_at' => 'datetime',
        'verified' => 'boolean',
        'used' => 'boolean', // Added from new code
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at',
    ];

    /**
     * Generate a unique token for email verification and associate with a user.
     *
     * @param User $user
     * @return EmailVerificationToken
     */
    public static function generateFor(User $user)
    {
        $token = Str::random(60);
        $verification = new self();
        $verification->token = $token;
        $verification->user_id = $user->id;
        $verification->created_at = now();
        $verification->save();

        return $verification;
    }

    /**
     * Check if the token is valid, has not expired, and has not been used.
     *
     * @return bool
     */
    public function isValid()
    {
        return $this->expires_at->isFuture() &&
               !$this->verified &&
               !$this->used && // Added from new code
               $this->created_at->diffInMinutes(now()) <= config('auth.verification.expiration', 60);
    }

    /**
     * Get the user that owns the email verification token.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Mark the token as used after successful verification.
     *
     * @return void
     */
    public function markAsUsed()
    {
        $this->update(['used' => true]);
    }
}
