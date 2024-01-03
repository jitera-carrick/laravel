<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class EmailVerificationToken extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'email_verification_tokens';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'token',
        'expires_at',
        'used',
        'user_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'expires_at' => 'datetime',
        'used' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user that owns the email verification token.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Generate a unique token for email verification and associate it with the user.
     *
     * @param User $user
     * @return EmailVerificationToken
     */
    public static function generateFor(User $user)
    {
        $token = new self;
        $token->user_id = $user->id;
        $token->token = Str::random(60);
        $token->expires_at = now()->addMinutes(60); // Token expires in 1 hour
        $token->used = false;
        $token->save();

        return $token;
    }
}
