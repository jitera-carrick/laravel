
<?php

namespace App\Services;

use App\Models\Session;
use Illuminate\Support\Facades\Config;

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

    public function invalidateSession($session_token)
    {
        $session = Session::where('session_token', $session_token)->first();
        if ($session && $session->is_active) {
            $session->is_active = false;
            $session->expires_at = now();
            $session->save();
            return true;
        }
        return false;
    }
}
