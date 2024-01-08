
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

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

    /**
     * Generate a unique password reset token for a user.
     *
     * @param string $email
     * @return PasswordResetToken
     */
    public static function generateUniqueResetToken($email)
    {
        $token = Str::random(60);
        $expiresAt = now()->addHours(2); // Set expiration time to 2 hours from now

        // Ensure token is unique
        while (self::where('token', $token)->exists()) {
            $token = Str::random(60);
        }

        return self::createToken($email, $token, $expiresAt);
    }
}
