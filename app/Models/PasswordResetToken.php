<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordResetToken extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email',
        'token',
        'created_at',
        'expires_at',
        'used',
        'user_id',
    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The user that the password reset token belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Create a new password reset token entry.
     *
     * @param string $email
     * @param string $token
     * @param \DateTime $expiresAt
     * @return PasswordResetToken
     */
    public static function createToken($email, $token, $expiresAt)
    {
        return self::create([
            'email' => $email,
            'token' => $token,
            'created_at' => now(),
            'expires_at' => $expiresAt,
            'used' => false,
        ]);
    }
}
