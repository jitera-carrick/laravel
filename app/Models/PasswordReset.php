<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordReset extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'password_resets';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email',
        'token',
        'created_at', // Added from new code
        'user_id',
    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * Find a token record in the database.
     * Added from new code
     */
    public function scopeFindByToken($query, $token)
    {
        return $query->where('token', $token);
    }

    /**
     * Check if the token is valid based on the expiration time.
     * Added from new code
     */
    public function isValid()
    {
        $expirationTime = config('auth.passwords.users.expire') * 60;
        return $this->created_at->addSeconds($expirationTime) > now();
    }

    /**
     * Delete the token record after use.
     * Added from new code
     */
    public function deleteToken()
    {
        $this->delete();
    }

    /**
     * Get the user that owns the password reset token.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Create or update a password reset entry for the given email.
     * Existing code
     *
     * @param string $email
     * @param string $token
     * @return PasswordReset
     */
    public static function createOrUpdate($email, $token)
    {
        $passwordReset = self::firstOrNew(['email' => $email]);
        $passwordReset->token = $token;
        $passwordReset->created_at = now(); // Ensure 'created_at' is set for new code compatibility
        $passwordReset->save();

        return $passwordReset;
    }
}
