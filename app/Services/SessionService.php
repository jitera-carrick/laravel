
<?php

namespace App\Services;

use App\Models\Session;
use App\Models\LoginAttempt;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Auth;

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
