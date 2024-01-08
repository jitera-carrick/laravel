
<?php

namespace App\Services;

use App\Models\Session;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
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

    public function generateSessionToken()
    {
        return Str::random(60);
    }

    public function hashSessionToken($sessionToken)
    {
        return Hash::make($sessionToken);
    }

    public function validateSessionToken($sessionToken)
    {
        $session = Session::where('session_token', $sessionToken)->first();
        return $session && $session->is_active && $session->expires_at > now();
    }

    public function createSession($userId, $sessionToken, $expiresAt)
    {
        return Session::createNewSession($userId, $sessionToken, $expiresAt);
    }
}
