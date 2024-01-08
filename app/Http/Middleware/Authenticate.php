
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
            // No session token provided, user is not authenticated
            return null;
        }

        $session = Session::where('session_token', $sessionToken)
                          ->where('expires_at', '>', now())
                          ->where('is_active', true)
                          ->first();

        // If the session is not valid, the user is not authenticated
        if (!$session) {
            return null;
        }

        // If the session is valid, the user is considered authenticated
        // No need to return anything as the user is allowed to proceed
    }
}
