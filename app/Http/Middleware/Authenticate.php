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
            if ($request->expectsJson()) {
                abort(response()->json([
                    'message' => 'Unauthorized, token not provided or expired.',
                ], 401));
            }
            return null;
        }

        $session = Session::where('session_token', $sessionToken)
                          ->where('expires_at', '>', now())
                          ->where('is_active', true)
                          ->first();

        if (!$session) {
            return null;
        if (!$session && $request->expectsJson()) {
            abort(response()->json([
                'message' => 'Unauthorized, session not found or expired.',
            ], 401));
        }
        }
    }
}
