<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

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
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        // Usually, tokens should be hidden for security reasons
        'token',
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
    ];

    /**
     * Get the user that owns the password reset request.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Store a new password reset request entry.
     *
     * @param int $userId
     * @param string $token
     * @return PasswordResetRequest
     */
    public static function storeRequest($userId, $token)
    {
        return self::create([
            'user_id' => $userId,
            'token' => $token,
            'expires_at' => Carbon::now()->addHours(24),
            'status' => 'pending', // Assuming 'pending' is a valid status
        ]);
    }
}
