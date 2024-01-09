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
        // 'expires_at', // Removed because it's included in casts
        'created_at',
        'updated_at',
    ];

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
