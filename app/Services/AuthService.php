
<?php

namespace App\Services;

use App\Models\User;
use App\Helpers\TokenHelper;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function login($email, $password, $keepSession)
    {
        $user = User::where('email', $email)->first();
        if (!$user || !Hash::check($password, $user->password_hash)) {
            throw new \Exception('Invalid credentials.');
        }

        $tokenHelper = new TokenHelper();
        $sessionToken = $tokenHelper->generateSessionToken();
        $sessionExpiration = $tokenHelper->calculateSessionExpiration($keepSession);

        $user->session_token = $sessionToken;
        $user->session_expiration = $sessionExpiration;
        $user->keep_session = $keepSession;
        $user->save();

        return $sessionToken;
    }

    public function terminateLoginProcess($userId)
    {
        $loginAttempts = LoginAttempt::where('user_id', $userId)
                                     ->where('successful', false)
                                     ->get();

        foreach ($loginAttempts as $attempt) {
            $attempt->delete();
        }
    }
}
