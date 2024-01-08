
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str; // Added to use Str::random

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
        'verified', // Added from new code
        'user_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'expires_at' => 'datetime',
        'verified' => 'boolean', // Added from new code
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
