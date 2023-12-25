<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;

class ValidateSessionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Retrieve the "session_token" from the request
        $sessionToken = $request->header('session_token');

        // Check if the session token is provided
        if (!$sessionToken) {
            return new JsonResponse(['error' => 'Session token not provided'], 401);
        }

        // Use the `User` model to find the user by the "session_token"
        $user = User::where('session_token', $sessionToken)->first();

        // Check if the user exists and the "token_expiration" is in the future
        if ($user && $user->token_expiration && $user->token_expiration->isFuture()) {
            // If the session is valid, add the "user_id" to the response and set "session_valid" to true
            $request->attributes->add(['user_id' => $user->id]); // Add user_id to the request attributes
            $response = $next($request);
            return $response->header('session_valid', 'true');
        } else {
            // If the session is invalid, set "session_valid" to false and do not include "user_id"
            return new JsonResponse(['error' => 'Invalid session token or token expired', 'session_valid' => false], 401);
        }
    }
}
