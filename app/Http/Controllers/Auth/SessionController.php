<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\SessionRequest;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource as UserResource;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Exception;

class SessionController extends Controller
{
    // ... (other methods)

    public function maintainUserSession(SessionRequest $request)
    {
        $validated = $request->validated();

        // Check if session_token is provided
        if (empty($validated['session_token'])) {
            return response()->json(['error' => 'Session token is required.'], 422);
        }

        try {
            $user = User::where('session_token', $validated['session_token'])->first();

            if (!$user) {
                return response()->json(['error' => 'User not found.'], 404);
            }

            // Check if the session is still active
            if (Carbon::now()->lt($user->session_expires)) {
                // Update the session_expires field based on the keep_session flag
                $newSessionExpires = $validated['keep_session'] ? Carbon::now()->addDays(90) : Carbon::now()->addDay();
                $user->session_expires = $newSessionExpires;
                $user->save();

                // Return the updated session expiration information
                return new UserResource($user->only('session_expires'));
            } else {
                return response()->json(['error' => 'Session expired.'], 401);
            }
        } catch (Exception $e) {
            // Here you should handle the exception as per your application's exception handling policy
            // For example, you could use a custom exception class and throw that
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
