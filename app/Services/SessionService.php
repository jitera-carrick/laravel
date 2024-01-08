<?php

namespace App\Services;

use App\Models\Session;
use Illuminate\Support\Facades\Config;

class SessionService
{
    protected $sessionLifetime;

    public function __construct()
    {
        $this->sessionLifetime = $this->getSessionLifetime();
    }

    public function maintain($session_token)
    {
        $session = Session::where('session_token', $session_token)->first();
        if ($session && $session->is_active) {
            $session->expires_at = now()->addMinutes($this->sessionLifetime);
            $session->save();
            return true;
        }
        return false;
    }

    public function deleteSession(string $session_token)
    {
        $session = Session::where('session_token', $session_token)->first();
        if ($session) {
            return $session->delete();
        }
        return false;
    }

    protected function getSessionLifetime()
    {
        return Config::get('session.lifetime', 120);
    }
}
