<?php

namespace App\Http\Middleware;

use App\Models\Session;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        if (!$request->expectsJson()) {
            return route('login');
        }

        $sessionToken = $request->bearerToken() ?? $request->cookie('session_token');

        if (!$sessionToken) {
            return null;
        }

        $session = Session::where('session_token', $sessionToken)
                          ->where('expires_at', '>', now())
                          ->where('is_active', true)
                          ->first();

        if ($session && $session->expires_at <= now()) {
            $session->is_active = false;
            $session->save();
            return null;
        }

        if (!$session) {
            return null;
        }
    }
}
