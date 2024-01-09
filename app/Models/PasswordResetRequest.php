
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PasswordResetRequest extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'password_reset_requests';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'token',
        'expires_at',
        'status',
        'user_id',
        'reset_token',
        'token_expiration',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'reset_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'expires_at' => 'datetime',
        'token_expiration' => 'datetime',
        'status' => 'string',
    ];

    /**
     * Get the user that owns the password reset request.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // ... existing methods ...

    /**
     * Create a new password reset request entry.
     *
     * @param string $email
     * @return string
     */
    public static function createRequest($email)
    {
        $user = User::where('email', $email)->first();
        $resetToken = bin2hex(random_bytes(64));
        $passwordResetRequest = new self([
            'user_id' => $user->id,
            'reset_token' => $resetToken,
            'token_expiration' => now()->addHour(),
        ]);
        $passwordResetRequest->save();

        return $resetToken;
    }
}
