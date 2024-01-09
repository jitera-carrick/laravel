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
        'user_id',
        // Note: 'created_at' and 'updated_at' are managed by Eloquent and should not be added to the fillable array.
    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * Check if the password reset token is expired.
     *
     * @return bool
     */
    public function isTokenExpired()
    {
        $expirationTime = config('auth.passwords.users.expire') * 60;
        return $this->created_at->addSeconds($expirationTime)->isPast();
    }

    /**
     * Find a password reset entry by token and email.
     *
     * @param string $token
     * @param string $email
     * @return PasswordReset|null
     */
    public static function findByTokenAndEmail($token, $email)
    {
        return self::where('token', $token)->where('email', $email)->first();
    }

    /**
     * Get the user that owns the password reset token.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
