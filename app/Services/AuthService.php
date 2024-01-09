
<?php

namespace App\Services;

use Illuminate\Auth\AuthenticationException;
use App\Models\User;
use App\Helpers\TokenHelper;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function login($email, $password, $keepSession)
    {
        $user = User::where('email', $email)->first();
        if (!$user) {
            throw new AuthenticationException('User does not exist.');
        }

        if (!Hash::check($password, $user->password_hash)) {
            throw new \Exception('Invalid credentials.');
        }

        $tokenHelper = new TokenHelper();
        $sessionToken = $tokenHelper->generateSessionToken();
        $sessionExpiration = $tokenHelper->calculateSessionExpiration($keepSession);

        $user->session_token = $sessionToken;
        $user->session_expiration = $sessionExpiration->toDateTimeString();
        $user->keep_session = $keepSession;
        $user->save();

        return $sessionToken;
    }
}
