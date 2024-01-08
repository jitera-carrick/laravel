
<?php

namespace App\Services;

use App\Models\Session;
use Illuminate\Support\Facades\Config;
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

    public function createSession($userId)
    {
        $sessionToken = Str::random(60);
        $expiresAt = now()->addMinutes(Config::get('session.lifetime'));

        $session = new Session();
        $session->user_id = $userId;
        $session->session_token = $sessionToken;
        $session->created_at = now();
        $session->expires_at = $expiresAt;
        $session->is_active = true;
        $session->save();

        return $sessionToken;
    }
}
