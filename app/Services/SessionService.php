
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

    public function findSessionByToken($session_token)
    {
        return Session::where('session_token', $session_token)->first();
    }
}
