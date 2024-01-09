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
        // The new code adds 'created_at' and 'updated_at' to the fillable array.
        // However, since the model uses timestamps, these fields are automatically
        // managed by Eloquent, so they do not need to be explicitly added to the fillable array.
        // We will not include 'created_at' and 'updated_at' in the fillable array to avoid potential issues.
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
}
