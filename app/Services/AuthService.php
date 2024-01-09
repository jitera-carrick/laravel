<?php

namespace App\Services;

use App\Models\User;
use App\Helpers\TokenHelper;
use Illuminate\Support\Facades\Hash;
use App\Exceptions\InvalidCredentialsException;
use Illuminate\Auth\AuthenticationException;

class AuthService
{
    public function login($email, $password, $keepSession)
    {
        $user = User::where('email', $email)->first();
        if (!$user) {
            throw new AuthenticationException('User does not exist.');
        }

        if (!Hash::check($password, $user->password_hash)) {
            // Here we are using the new exception class InvalidCredentialsException
            // instead of the generic \Exception class for more specific error handling.
            throw new InvalidCredentialsException('The provided credentials do not match our records.');
        }

        $tokenHelper = new TokenHelper();
        $sessionToken = $tokenHelper->generateSessionToken();
        $sessionExpiration = $tokenHelper->calculateSessionExpiration($keepSession);

        // The new code does not convert the expiration to a DateTime string,
        // but to maintain consistency with the existing code, we keep the conversion.
        $user->session_token = $sessionToken;
        $user->session_expiration = $sessionExpiration->toDateTimeString();
        $user->keep_session = $keepSession;
        $user->save();

        return $sessionToken;
    }
}
