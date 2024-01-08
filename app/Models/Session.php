
<?php

use Illuminate\Support\Facades\Config;
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        'session_token',
        'expires_at',
        'is_active',
        'user_id',
        'token', // New column added to fillable
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        // Usually, session tokens are sensitive and should be hidden by default.
        'session_token',
        'token', // New column added to hidden
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
            'is_active' => true
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
     * Get the user that owns the session.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Invalidate the session.
     *
     * @return bool
     */
    public function invalidateSession()
    {
        $this->is_active = false;
        return $this->save();
    }
}
