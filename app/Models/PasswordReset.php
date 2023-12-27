<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PasswordReset extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'password_resets';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'reset_token',
        'expiration',
        'status',
        'user_id',
        'email', // Added new column 'email' to fillable
        'token', // Added new column 'token' to fillable
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'reset_token', // Assuming we want to hide the reset token
        'token', // Assuming we want to hide the token as well
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime', // Added cast for 'created_at'
        'updated_at' => 'datetime', // Added cast for 'updated_at'
        'expiration' => 'datetime', // Added cast for 'expiration'
    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true; // Changed to true to handle 'created_at' and 'updated_at'

    /**
     * Get the user that owns the password reset.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
