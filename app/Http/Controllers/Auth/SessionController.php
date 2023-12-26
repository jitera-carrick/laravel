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

        // Resolve the conflict by combining the validation logic from both versions of the code.
        // We need to check for 'email' as per the existing code and 'session_token' as per the new code.
        if (empty($validated['email']) || empty($validated['session_token'])) {
            return response()->json(['error' => 'Email and session token are required.'], 422);
        }

        try {
            // Modify the query to find the user by both email and session_token.
            // This combines the logic from both versions of the code.
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

                // Combine the response logic from both versions of the code.
                // Exclude sensitive information from the response as per the new code,
                // and include the 'message' from the existing code.
                $response = $user->only(['id', 'name', 'email', 'session_expires']);
                return response()->json([
                    'message' => 'Session updated successfully.',
                    'user' => $response
                ], 200);
            } else {
                return response()->json(['error' => 'Session expired.'], 401);
            }
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // ... (other methods)

    public function verifyEmail($id, $verification_token)
    {
        // ... (existing code)
    }

    public function updateUserProfile(Request $request)
    {
        // ... (existing code)
    }

    // ... (other existing methods)
}
