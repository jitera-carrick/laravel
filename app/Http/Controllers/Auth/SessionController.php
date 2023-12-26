<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\SessionRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Resources\Json\JsonResource as UserResource;
use Exception;

class SessionController extends Controller
{
    // ... (other methods)

    public function maintainUserSession(SessionRequest $request)
    {
        $validated = $request->validated();

        // Check if the user is authenticated and get the authenticated user
        $user = Auth::user();

        // If the user is not authenticated or the session token does not match, return unauthorized
        if (!$user || $user->session_token !== $validated['session_token']) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // If the session has not expired, update the session expiration time
        if (Carbon::now()->lt($user->session_expires)) {
            $user->session_expires = $validated['keep_session']
                ? Carbon::now()->addDays(90)
                : Carbon::now()->addDay();
            $user->save();

            // Return only the necessary user information
            $response = $user->only(['id', 'name', 'email', 'session_expires']);
            return response()->json([
                'message' => 'Session updated successfully.',
                'user' => $response
            ], 200);
        } else {
            // If the session has expired, return session expired error
            return response()->json(['error' => 'Session expired.'], 401);
        }
    }

    public function logout(Request $request)
    {
        try {
            $validated = $request->validate(['session_token' => 'required|string']);

            $user = User::where('session_token', $validated['session_token'])->first();

            if (!$user) {
                return response()->json(['message' => 'Invalid session token.'], 401);
            }

            $user->session_token = null;
            $user->session_expires = Carbon::now();
            $user->save();

            return response()->json(['status' => 200, 'message' => 'Logout successful.']);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function validateUserSession(Request $request)
    {
        $sessionToken = $request->query('session_token');

        if (!$sessionToken) {
            return response()->json(['error' => 'Session token is required.'], 400);
        }

        try {
            $user = User::where('session_token', $sessionToken)->first();

            if ($user && $user->session_expires && $user->session_expires->isFuture()) {
                return response()->json([
                    'status' => 200,
                    'message' => 'Session is valid.',
                    'user_id' => $user->id
                ], 200);
            } else {
                return response()->json(['error' => 'Invalid session token.'], 401);
            }
        } catch (Exception $e) {
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }

    public function verifyEmail($id, $verification_token)
    {
        // ... (existing code for verifyEmail)
    }

    public function updateUserProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $request->user()->id,
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = $request->user();

        $user->name = $request->input('name');
        $user->email = $request->input('email');
        $user->save();

        return response()->json([
            'status' => 200,
            'message' => 'Profile updated successfully.',
            'user' => $user->only(['id', 'name', 'email', 'updated_at'])
        ], 200);
    }

    // ... (other existing methods)

    // This method is added from the new code to handle session updates
    public function updateUserSession(Request $request)
    {
        $validated = $request->validate([
            'session_token' => 'required|string',
            'keep_session' => 'required|boolean',
        ]);

        try {
            $user = User::where('session_token', $validated['session_token'])->first();

            if (!$user) {
                return response()->json(['message' => 'Invalid session token.'], 404);
            }

            $newSessionExpires = $validated['keep_session'] ? Carbon::now()->addDays(90) : Carbon::now()->addDay();
            $user->session_expires = $newSessionExpires;
            $user->save();

            return response()->json([
                'status' => 200,
                'message' => 'Session updated successfully.',
                'session_expires' => $user->session_expires->toIso8601String(),
            ], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Internal Server Error'], 500);
        }
    }
}
