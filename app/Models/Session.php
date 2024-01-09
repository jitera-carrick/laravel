
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;

class Session extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'sessions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'token',
        'expires_at',
        'user_id',
        'session_token',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'session_token',
        'token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * The user that the session belongs to.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Create a new session record.
     *
     * @param int $userId
     * @param string $sessionToken
     * @param \DateTime $expiresAt
     * @return Session
     */
    public function createNewSession($userId, $sessionToken, $expiresAt)
    {
        return $this->create([
            'user_id' => $userId,
            'session_token' => $sessionToken,
            'created_at' => now(),
            'expires_at' => $expiresAt,
            'is_active' => true,
            'token' => $sessionToken,
        ]);
    }

    /**
     * Maintain the session based on the provided session token.
     *
     * @param string $sessionToken
     * @return bool
     */
    public function maintainSession($sessionToken)
    {
        $session = $this->where('session_token', $sessionToken)->first();
        if ($session && $session->is_active && $session->expires_at > now()) {
            $session->expires_at = now()->addMinutes(Config::get('session.lifetime'));
            return $session->save();
        }
        return false;
    }

    /**
     * Invalidate the session based on the provided session token.
     *
     * @param string $sessionToken
     * @return bool
     */
    public function invalidateSession($sessionToken)
    {
        $session = $this->where('session_token', $sessionToken)->first();
        if ($session && $session->is_active) {
            $session->is_active = false;
            return $session->save();
        }
        return false;
    }
}
