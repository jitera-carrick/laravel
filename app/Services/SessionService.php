<?php

namespace App\Services;

use App\Models\Session;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

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

    public function login(array $data)
    {
        $validator = Validator::make($data, [
            'email' => 'required|email',
            'password' => 'required',
            'keep_session' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $user = User::where('email', $data['email'])->first();
        if (!$user || !Hash::check($data['password'], $user->password)) {
            throw new AuthenticationException('Unauthorized', 401);
        }

        $keepSession = $data['keep_session'] ?? false;
        $sessionToken = bin2hex(random_bytes(30));
        $sessionExpiration = $keepSession ? now()->addYear() : now()->addHours(2);

        $session = new Session([
            'user_id' => $user->id,
            'session_token' => $sessionToken,
            'expires_at' => $sessionExpiration,
            'is_active' => true,
        ]);
        $session->save();

        return [
            'status' => 200,
            'message' => 'Login successful.',
            'session_token' => $sessionToken,
            'session_expiration' => $sessionExpiration->toIso8601String(),
        ];
    }

    public function cancelOngoingLogin(): void
    {
        // Assuming Session model has a method to find and invalidate active sessions
        $activeSessions = Session::where('is_active', true)->get();
        foreach ($activeSessions as $session) {
            $session->is_active = false;
            $session->save();
        }
    }
}
