
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
     * Get the user that owns the password reset token.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
