<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Carbon\Carbon;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        return $request->expectsJson() ? null : route('login');
    }

    public function handle($request, Closure $next, ...$guards)
    {
        // Before proceeding with the request, check if it's a cancel login request
        if ($this->isCancelLoginRequest($request)) {
            return $this->handleCancelLoginRequest($request);
        }

        // Check for session maintenance before proceeding with the request
        $maintainSessionResponse = $this->maintainSession($request);
        if ($maintainSessionResponse instanceof \Illuminate\Http\JsonResponse && $maintainSessionResponse->getStatusCode() !== 200) {
            return $maintainSessionResponse;
        }

        // Existing authentication logic...
        return parent::handle($request, $next, ...$guards);
    }

    /**
     * Determine if the request is for cancelling the login process.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function isCancelLoginRequest($request)
    {
        // Check for a specific route or request parameter that indicates a cancel action
        // This is just an example, adjust the logic based on how you detect the cancel action
        return $request->routeIs('cancel-login');
    }

    /**
     * Handle a cancel login request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function handleCancelLoginRequest($request)
    {
        // Redirect to the 'screen-tutorial' without altering the session or auth state
        return redirect()->route('screen-tutorial');
    }

    /**
     * Handle the maintenance of user sessions.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function maintainSession(Request $request)
    {
        $sessionToken = $request->input('session_token');

        if (empty($sessionToken)) {
            return response()->json(['error' => 'Session token is required.'], 400);
        }

        $user = Auth::guard()->getUserProvider()->retrieveByCredentials(['session_token' => $sessionToken]);

        if (!$user) {
            return response()->json(['error' => 'Invalid session token.'], 404);
        }

        $currentDateTime = Carbon::now();
        if ($user->session_expiration && $currentDateTime->lessThan($user->session_expiration)) {
            if ($user->session_expiration->diffInDays($currentDateTime) < 90) {
                $user->session_expiration = $currentDateTime->addDays(90);
                $user->save();

                // Update session lifetime configuration
                Config::set('session.lifetime', 90 * 24 * 60);

                // Update last_activity timestamp in session storage
                session(['last_activity' => $currentDateTime->timestamp]);

                return response()->json(['message' => 'Session has been successfully maintained.'], 200);
            }
        }

        return response()->json(['error' => 'Session is no longer valid.'], 403);
    }
}
