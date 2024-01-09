<?php

namespace App\Http\Middleware;

use App\Http\Responses\ApiResponse;
use App\Models\Session;
use App\Models\User; // Added User model
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

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

        // Check if the request is for creating a hair stylist request
        if ($request->is('hair-stylist-requests') && $request->method() === 'POST') {
            return new JsonResponse([
                'error' => 'Unauthenticated.',
                'message' => 'You must be logged in to create a hair stylist request.'
            ], 401);
        }

        $sessionToken = $request->bearerToken() ?? $request->cookie('session_token');

        if (!$sessionToken) {
            // Use ApiResponse if it's available, otherwise return a JsonResponse
            if (class_exists(ApiResponse::class)) {
                return ApiResponse::loginFailure()->getContent();
            } else {
                return new JsonResponse(['error' => 'Session token not provided.'], 401);
            }
        }

        $session = Session::where('session_token', $sessionToken)
                          ->where('expires_at', '>', now())
                          ->where('is_active', true)
                          ->first();

        // If session is not found, check for user session token
        if (!$session) {
            $user = User::where('session_token', $sessionToken) // Added code block
                         ->where('session_expiration', '>', now())
                         ->first();

            if (!$user) {
                return new JsonResponse([
                    'error' => 'Invalid session token or session has expired.',
                    'message' => 'Please login again to continue.'
                ], 401);
            }
        }

        if (!$session && !$user) {
            return null;
        }
    }
}
