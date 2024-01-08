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
        'verified',
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
     * Create a new token for a user.
     *
     * @param int $userId
     * @return EmailVerificationToken
     */
    public static function createForUser($userId)
    {
        $token = new self;
        $token->user_id = $userId;
        $token->token = bin2hex(openssl_random_pseudo_bytes(16));
        $token->expires_at = now()->addHours(24);
        $token->verified = false;

        $token->save();

        return $token;
    }

    /**
     * Mark the email verification token as verified if it is valid.
     *
     * @return bool
     */
    public function markAsVerified()
    {
        if (!$this->isValid()) {
            return false;
        }

        $this->verified = true;
        $this->save();
        return true;
    }

    /**
     * Check if the token is valid and has not expired.
     *
     * @return bool
     */
    public function isValid()
    {
        return $this->expires_at->isFuture();
    }

    /**
     * Get the user that owns the email verification token.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
