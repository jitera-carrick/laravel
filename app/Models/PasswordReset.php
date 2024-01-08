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
    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * Get the user that owns the password reset token.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Create or update a password reset entry for the given email.
     *
     * @param string $email
     * @param string $token
     * @return PasswordReset
     */
    public static function createOrUpdate($email, $token)
    {
        $passwordReset = self::firstOrNew(['email' => $email]);
        $passwordReset->token = $token;
        $passwordReset->created_at = now();
        $passwordReset->save();

        return $passwordReset;
    }
}
