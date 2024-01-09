
<?php

namespace App\Services;

use App\Models\Session;
use App\Models\User;
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

    public function createSessionForUser(User $user)
    {
        $session = new Session();
        $session->user_id = $user->id;
        $session->session_token = bin2hex(openssl_random_pseudo_bytes(30)); // Generate a unique token
        $session->expires_at = now()->addMinutes(Config::get('session.lifetime', 120)); // Set expiration based on config
        $session->save();

        return $session;
    }

    // Other methods...

    // New methods can be added below as needed.
}
