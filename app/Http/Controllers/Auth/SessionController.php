<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\SessionRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource as UserResource;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Exception;

class SessionController extends Controller
{
    // ... (other methods)

    public function maintainUserSession(SessionRequest $request)
    {
        // Using SessionRequest's validated method to ensure the data is validated
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
            // Validate the request
            $validated = $request->validate(['session_token' => 'required|string']);

            // Find the user with the given session token
            $user = User::where('session_token', $validated['session_token'])->first();

            // If the user is not found, return an error
            if (!$user) {
                return response()->json(['error' => 'User not found.'], 404);
            }

            // Clear the session token and expiration
            $user->session_token = null;
            $user->session_expires = Carbon::now();
            $user->save();

            // Return a successful logout response
            return response()->json(['message' => 'Logged out successfully.']);
        } catch (Exception $e) {
            // If there is an exception, return an error
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function validateUserSession(Request $request)
    {
        // ... (existing code for validateUserSession)
    }

    public function verifyEmail($id, $verification_token)
    {
        // ... (existing code for verifyEmail)
    }

    public function updateUserProfile(Request $request)
    {
        // Use Validator facade to validate the request
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $request->user()->id,
        ]);

        // If validation fails, return an error response
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Get the authenticated user
        $user = $request->user();

        // Update the user's name and email
        $user->name = $request->input('name');
        $user->email = $request->input('email');
        $user->save();

        // Return a successful profile update response
        return response()->json([
            'status' => 200,
            'message' => 'Profile updated successfully.',
            'user' => $user->only(['id', 'name', 'email', 'updated_at'])
        ], 200);
    }

    // ... (other existing methods)
}
