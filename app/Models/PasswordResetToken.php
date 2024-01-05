
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
     * Generate a unique token for password reset.
     *
     * @return string
     */
    public static function generateUniqueToken()
    {
        return Str::random(60);
    }

    /**
     * Set the token attributes.
     *
     * @param array $attributes
     * @return void
     */
    public function setTokenAttributes(array $attributes)
    {
        $this->fill($attributes);
    }

    /**
     * Mark the token as used.
     *
     * @return void
     */
    public function markAsUsed()
    {
        $this->used = true;
        $this->save();
    }
}
