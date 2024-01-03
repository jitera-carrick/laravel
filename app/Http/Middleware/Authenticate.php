<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use App\Models\User;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        return $request->expectsJson() ? null : route('login');

    public function handle(Request $request, Closure $next, ...$guards)
    {
        $sessionToken = $request->input('session_token');
        if (!$sessionToken) {
            // Return an error response if the session token is not provided
            return response()->json(['message' => 'Session token is required.'], 400);
        }

        $user = User::where('session_token', $sessionToken)->first();
        if ($user && $user->session_expiration->isFuture()) {
            // Extend the session expiration
            $keepSession = $request->input('keep_session', false);
            $user->session_expiration = $keepSession ? Carbon::now()->addDays(90) : Carbon::now()->addDay();
            $user->save();

            // Allow the request to proceed
            return $next($request);
        }

        // Return an error response if the session is not valid or has expired
        return response()->json(['message' => 'Invalid or expired session token.'], 401);
    }
    }
}
