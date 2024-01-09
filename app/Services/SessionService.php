<?php

namespace App\Services;

use App\Models\User;
use App\Models\Session;
use App\Models\LoginAttempt;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class SessionService
{
    public function maintain($session_token)
    {
        $session = Session::where('session_token', $session_token)->first();
        if ($session && $session->is_active && $session->expires_at > now()) {
            $session->updated_at = now();
            return $session->save();
        }
        return false;
    }

    public function createSessionToken(User $user, bool $keep_session)
    {
        $session_token = Str::random(60);
        $sessionConfig = Config::get('session');

        $session_expiration = $keep_session
            ? now()->addDays(90)
            : now()->addHours($sessionConfig['lifetime']);

        return [
            'session_token' => $session_token,
            'session_expiration' => $session_expiration,
        ];
    }

    public function cancelOngoingLogin($userId = null)
    {
        $userId = $userId ?: Auth::id();

        if (!$userId) {
            throw new \Exception('User ID is required');
        }

        $loginAttempts = LoginAttempt::where('user_id', $userId)->whereNull('successful')->get();

        foreach ($loginAttempts as $attempt) {
            $attempt->delete();
        }

        return $loginAttempts->isNotEmpty();
    }
}
