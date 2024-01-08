
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Session;
use App\Exceptions\SessionExpiredException;

class VerifySessionToken
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $sessionToken = $request->header('session_token');

        if (!$sessionToken) {
            return response()->json(['error' => 'Session token is required'], 401);
        }

        $session = Session::maintainSession($sessionToken);

        if (!$session) {
            throw new SessionExpiredException('Session has expired, please log in again.');
        }

        return $next($request);
    }
}
