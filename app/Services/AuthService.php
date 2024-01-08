<?php

namespace App\Services;

use App\Models\User;
use App\Models\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Exception;

class AuthService
{
    public function attemptLogin($email, $password)
    {
        $user = User::where('email', $email)->first();
        if (!$user || !Hash::check($password, $user->password)) {
            throw new Exception('Invalid credentials.');
        }

        $jwtConfig = config('jwt');
        $payload = [
            'iss' => "lumen-jwt", // Issuer of the token
            'sub' => $user->id, // Subject of the token
            'iat' => time(), // Time when JWT was issued.
            'exp' => time() + $jwtConfig['ttl'] // Expiration time
        ];

        $token = JWT::encode($payload, $jwtConfig['secret'], 'HS256');

        return $token;
    }

    public function encryptPassword($password)
    {
        return Hash::make($password);
    }

    public function validateResetToken($token)
    {
        $passwordReset = PasswordReset::where('token', $token)->first();
        if (!$passwordReset) {
            throw new Exception("Invalid token.");
        }

        $tokenLifeTime = config('auth.passwords.users.expire') * 60;
        if (now()->subSeconds($tokenLifeTime)->isAfter($passwordReset->created_at)) {
            throw new Exception("Token has expired.");
        }

        return true;
    }

    public function refreshSessionToken($currentToken)
    {
        try {
            $jwtConfig = config('jwt');
            $decodedToken = JWT::decode($currentToken, new Key($jwtConfig['secret'], 'HS256'));
            if ($decodedToken->exp < time()) {
                throw new Exception('Session token has expired.');
            }

            $payload = [
                'iss' => $decodedToken->iss,
                'sub' => $decodedToken->sub,
                'iat' => time(),
                'exp' => time() + $jwtConfig['ttl']
            ];

            return JWT::encode($payload, $jwtConfig['secret'], 'HS256');
        } catch (Exception $e) {
            throw new Exception('Invalid session token.');
        }
    }
}
