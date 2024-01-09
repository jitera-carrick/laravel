
<?php

namespace App\Http\Middleware;

use App\Http\Responses\ApiResponse;
use App\Models\Session;
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
        if ($request->expectsJson()) {
            return ApiResponse::loginFailure()->getContent();
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
            return null;
        }

        $session = Session::where('session_token', $sessionToken)
                          ->where('expires_at', '>', now())
                          ->where('is_active', true)
                          ->first();

        if (!$session) {
            return null;
        }
    }
}
