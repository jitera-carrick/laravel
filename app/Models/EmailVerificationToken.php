
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
        'used', // The 'used' attribute should be included in the fillable array as per the new requirements
        'user_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'used' => 'boolean', // The 'used' attribute should be cast to 'boolean' as per the new requirements
        'expires_at' => 'datetime',
    ];

    /**
     * Check if the token is valid and has not expired.
     *
     * @return bool
     */
    public function isValid()
    {
        return !$this->used && $this->expires_at->isFuture(); // The 'used' check is added back as per the new requirements
    }

    /**
     * The user that the verification token belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
