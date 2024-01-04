
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\SessionRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class SessionController extends Controller
{
    // ... other methods ...

    /**
     * Maintain the user session based on the provided session token.
     *
     * @param SessionRequest $request
     * @return JsonResponse
     */
    public function maintainSession(SessionRequest $request): JsonResponse
    {
        $sessionToken = $request->input('session_token');
        $keepSession = $request->input('keep_session', false);

        $user = User::where('session_token', $sessionToken)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        if ($user->session_expiration && $user->session_expiration->gt(Carbon::now())) {
            $newExpiration = $keepSession ? Carbon::now()->addDays(90) : Carbon::now()->addDay();
            $user->session_expiration = $newExpiration;
            $user->save();

            return response()->json([
                'message' => 'Session maintained successfully.',
                'session_expiration' => $newExpiration->toDateTimeString(),
            ]);
        }

        return response()->json(['message' => 'Session has already expired.'], 401);
    }

    // ... other methods ...

    /**
     * Log out the user by invalidating the session token.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        $sessionToken = $request->bearerToken() ?? $request->input('session_token');

        $user = User::where('session_token', $sessionToken)->first();

        if ($user) {
            $user->session_token = null;
            $user->is_logged_in = false;
            $user->save();

            return response()->json(['message' => 'Successfully logged out.']);
        }

        return response()->json(['message' => 'Invalid session token or user not found.'], 401);
    }

    // ... other methods ...
}
