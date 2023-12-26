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

        if (empty($validated['email']) || empty($validated['session_token'])) {
            return response()->json(['error' => 'Email and session token are required.'], 422);
        }

        try {
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

    public function logout(Request $request)
    {
        try {
            $validated = $request->validate(['session_token' => 'required|string']);

            $user = User::where('session_token', $validated['session_token'])->first();

            if (!$user) {
                return response()->json(['error' => 'User not found.'], 404);
            }

            $user->session_token = null;
            $user->session_expires = Carbon::now();
            $user->save();

            return response()->json(['message' => 'Logged out successfully.']);
        } catch (Exception $e) {
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
}
