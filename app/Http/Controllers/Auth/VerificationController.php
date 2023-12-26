<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator; // Import the Validator facade

class VerificationController extends Controller
{
    public function verify(Request $request)
    {
        $email = $request->input('email');
        $rememberToken = $request->input('remember_token');

        try {
            $user = User::where('email', $email)
                        ->where('remember_token', $rememberToken)
                        ->first();

            if (!$user) {
                return response()->json(['message' => 'No matching user found or token does not match.'], 400);
            }

            $user->email_verified_at = Carbon::now();
            $user->save();

            return response()->json(['message' => 'Email successfully verified.']);
        } catch (\Exception $e) {
            Log::error('Email verification failed: ' . $e->getMessage());
            return response()->json(['message' => 'Email verification failed.'], 500);
        }
    }

    // New method to handle email verification logic
    public function verifyEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'token' => 'required|string',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            if ($errors->has('email')) {
                return response()->json(['message' => 'Invalid email format.'], 422);
            }
            if ($errors->has('token')) {
                return response()->json(['message' => 'Verification token is required.'], 422);
            }
        }

        try {
            $user = User::where('email', $request->input('email'))->first();

            if (!$user) {
                return response()->json(['message' => 'User not found.'], 404);
            }

            // Assuming the token is stored in a password_reset_tokens table
            $tokenRecord = $user->passwordResetTokens()->where('token', $request->input('token'))->first();

            if (!$tokenRecord || $tokenRecord->created_at->addMinutes(config('auth.passwords.users.expire'))->isPast()) {
                return response()->json(['message' => 'Invalid or expired verification token.'], 400);
            }

            $user->email_verified_at = Carbon::now();
            // Clear the verification token
            $tokenRecord->delete();
            $user->save();

            return response()->json(['status' => 200, 'message' => 'Email verified successfully.'], 200);
        } catch (\Exception $e) {
            Log::error('Email verification failed: ' . $e. getMessage());
            return response()->json(['message' => 'Email verification failed.'], 500);
        }
    }
}
