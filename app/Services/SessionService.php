<?php

namespace App\Services;

use App\Models\Session;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Http\Response;

class SessionService
{
    public function maintain($session_token)
    {
        $session = Session::where('session_token', $session_token)->first();
        if (!$session) {
            return response()->json(['message' => 'Invalid session token.'], Response::HTTP_UNAUTHORIZED);
        }

        if ($session->expires_at <= now()) {
            $session->is_active = false;
            $session->updated_at = now();
            $session->save();
            return response()->json(['message' => 'Session token has expired.'], Response::HTTP_UNAUTHORIZED);
        }

        if ($session->is_active) {
            // Update the expiration time of the session
            $session->expires_at = now()->addMinutes(Config::get('session.lifetime'));
            $session->updated_at = now();
            $session->save();
            return response()->json(['status' => 200, 'message' => 'Session maintained successfully.'], Response::HTTP_OK);
        }

        return response()->json(['message' => 'Invalid session token.'], Response::HTTP_UNAUTHORIZED);
    }

    public function createSession($userId)
    {
        $sessionToken = Str::random(60);
        $expiresAt = now()->addMinutes(Config::get('session.lifetime'));

        $session = new Session();
        $session->user_id = $userId;
        $session->session_token = $sessionToken;
        $session->created_at = now();
        $session->expires_at = $expiresAt;
        $session->is_active = true;
        $session->save();

        return $sessionToken;
    }

    public function deactivateSession($sessionToken)
    {
        $session = Session::where('session_token', $sessionToken)->first();
        if ($session) {
            $session->is_active = false;
            $session->updated_at = now();
            return $session->save();
        }
        return false;
    }

    public function validateSessionToken($session_token)
    {
        $session = Session::where('session_token', $session_token)->first();
        return $session && $session->is_active && $session->expires_at > Carbon::now();
    }
}
