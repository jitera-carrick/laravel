<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\SessionRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
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

    /**
     * Update the user's session preference based on the provided user ID.
     *
     * @param SessionRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function updateSessionPreference(SessionRequest $request, $id): JsonResponse
    {
        if (!is_numeric($id)) {
            return response()->json(['message' => 'Wrong format.'], 400);
        }

        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        if (!Auth::check() || Auth::id() !== (int) $id) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        $keepSession = $request->input('keep_session', false);
        $newExpiration = $keepSession ? Carbon::now()->addDays(90) : Carbon::now()->addDay();
        $user->session_expiration = $newExpiration;
        $user->save();

        return response()->json([
            'status' => 200,
            'message' => 'Session preference updated successfully.',
            'session_expiration' => $newExpiration->toDateTimeString(),
        ]);
    }

    // ... other methods ...
}
