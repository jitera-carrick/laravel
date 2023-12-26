<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB; // Make sure to use DB for database operations

class LoginAttempt extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'login_attempts';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'attempted_at',
        'success',
        'user_id',
        'status', // New column
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        // Usually, there's nothing to hide in login attempts, but you can add fields here if needed.
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'attempted_at' => 'datetime',
        'success' => 'boolean',
        'status' => 'string', // New column
    ];

    /**
     * Get the user that owns the login attempt.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Log a login attempt.
     *
     * @param int $userId
     * @param \DateTimeInterface $timestamp
     * @param bool $success
     * @return void
     */
    public static function logAttempt($userId, $timestamp, $success)
    {
        DB::table('login_attempts')->insert([
            'user_id' => $userId,
            'attempted_at' => $timestamp,
            'success' => $success,
            'status' => $success ? 'success' : 'failed', // Assuming 'status' can be 'success' or 'failed'
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}