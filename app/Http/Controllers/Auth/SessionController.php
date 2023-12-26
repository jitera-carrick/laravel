<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\SessionRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource as UserResource;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Exception;

class SessionController extends Controller
{
    // ... (other methods)

    public function maintainUserSession(SessionRequest $request)
    {
        $validated = $request->validated();

        // Validate the input to ensure that both "email" and "session_token" are provided and not empty.
        // This is a combination of the new and existing code to ensure both conditions are met.
        if (empty($validated['email']) || empty($validated['session_token'])) {
            return response()->json(['error' => 'Email and session token are required.'], 422);
        }

        try {
            // Modify the query to find the user by both email and session_token.
            // This is the new code logic that has been added.
            $user = User::where('email', $validated['email'])
                        ->where('session_token', $validated['session_token'])
                        ->first();

            if (!$user) {
                return response()->json(['error' => 'User not found or session token is invalid.'], 404);
            }

            if (Carbon::now()->lt($user->session_expires)) {
                $newSessionExpires = $validated['keep_session'] ? Carbon::now()->addDays(90) : Carbon::now()->addDay();
                $user->session_expires = $newSessionExpires;
                $user->save();

                // The response has been updated to match the new code's response format.
                return response()->json(['message' => 'Session updated successfully.', 'session_expires' => $user->session_expires], 200);
            } else {
                return response()->json(['error' => 'Session expired.'], 401);
            }
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // ... (other existing methods)

    // The rest of the existing methods from the existing code should remain unchanged.
    // They are not included here to avoid redundancy, but they should be present in the final file.
}
