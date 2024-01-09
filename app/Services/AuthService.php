
<?php

namespace App\Services;

use App\Models\User;
use App\Models\PasswordResetRequest;
use App\Helpers\TokenHelper;
use Illuminate\Support\Facades\Hash;
use App\Notifications\PasswordResetNotification;

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

    public function createPasswordResetRequest(User $user)
    {
        $tokenHelper = new TokenHelper();
        $resetToken = $tokenHelper->generateSessionToken();
        $tokenExpiration = now()->addHour();

        $passwordResetRequest = new PasswordResetRequest([
            'user_id' => $user->id,
            'reset_token' => $resetToken,
            'token_expiration' => $tokenExpiration,
        ]);
        $passwordResetRequest->save();

        $user->notify(new PasswordResetNotification($resetToken));

        return $passwordResetRequest;
    }
    
}
