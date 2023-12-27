<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordResetToken extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'password_reset_tokens';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'email';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email',
        'token',
        'created_at',
        'user_id',
    ];

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'created_at' => null, // Set default value if necessary
    ];

    /**
     * Get the user that owns the password reset token.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
