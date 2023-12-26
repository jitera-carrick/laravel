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

        if (empty($validated['session_token'])) {
            return response()->json(['error' => 'Session token is required.'], 422);
        }

        try {
            $user = User::where('session_token', $validated['session_token'])->first();

            if (!$user) {
                return response()->json(['error' => 'User not found.'], 404);
            }

            if (Carbon::now()->lt($user->session_expires)) {
                $newSessionExpires = $validated['keep_session'] ? Carbon::now()->addDays(90) : Carbon::now()->addDay();
                $user->session_expires = $newSessionExpires;
                $user->save();

                return new UserResource($user->only('session_expires'));
            } else {
                return response()->json(['error' => 'Session expired.'], 401);
            }
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function validateUserSession(Request $request)
    {
        $validated = $request->validate([
            'session_token' => 'required',
        ]);

        try {
            $user = User::where('session_token', $validated['session_token'])->first();

            if (!$user) {
                return response()->json(['error' => 'User not found.'], 404);
            }

            if (Carbon::now()->lt($user->session_expires)) {
                return response()->json([
                    'message' => 'Session is valid.',
                    'user_details' => $user->only(['id', 'name', 'email']),
                ]);
            } else {
                return response()->json(['error' => 'Session expired.'], 401);
            }
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function verifyEmail($id, $verification_token)
    {
        try {
            $user = User::find($id);

            if (!$user) {
                return response()->json(['error' => 'User not found.'], 404);
            }

            // Assuming the verification_token is stored in a column named 'email_verification_token'
            if ($user->email_verification_token === $verification_token) {
                $user->email_verified_at = Carbon::now();
                $user->save();

                return response()->json(['message' => 'Email verified successfully.'], 200);
            } else {
                return response()->json(['error' => 'Invalid verification token.'], 400);
            }
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function updateUserProfile(Request $request)
    {
        $userId = $request->input('id');
        $email = $request->input('email');
        $password = $request->input('password');
        $passwordConfirmation = $request->input('password_confirmation');

        if (!$userId || !$email || !$password || !$passwordConfirmation) {
            return response()->json(['error' => 'All fields are required.'], 422);
        }

        $user = User::find($userId);
        if (!$user) {
            return response()->json(['error' => 'User not found.'], 404);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return response()->json(['error' => 'Invalid email format.'], 422);
        }

        if ($user->email !== $email && User::where('email', $email)->where('id', '<>', $userId)->exists()) {
            return response()->json(['error' => 'Email already in use.'], 422);
        }

        if ($password !== $passwordConfirmation) {
            return response()->json(['error' => 'Password confirmation does not match.'], 422);
        }

        $user->email = $email;
        $user->password = Hash::make($password);
        $user->save();

        return response()->json(['message' => 'Profile updated successfully.']);
    }
}
