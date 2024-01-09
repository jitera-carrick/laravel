
<?php

namespace App\Services;

use App\Models\User;
use App\Http\Helpers\SessionHelper;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function attemptLogin(array $credentials)
    {
        $user = User::where('email', $credentials['email'])->first();
        if ($user && Hash::check($credentials['password'], $user->password_hash)) {
            $sessionToken = SessionHelper::generateSessionToken();
            $sessionExpiration = SessionHelper::calculateSessionExpiration($credentials['keep_session'] ?? false);
            $user->fill([
                'session_token' => $sessionToken,
                'session_expiration' => $sessionExpiration
            ])->save();
            return $sessionToken;
        }
        throw new \Exception("Login failed. Invalid credentials.");
    }
}
