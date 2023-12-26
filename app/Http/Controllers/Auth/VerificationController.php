<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class VerificationController extends Controller
{
    // This method is from the existing code and has been modified to include the new code logic
    public function verify($id, $verification_token = null)
    {
        if ($verification_token) {
            // This block is from the new code
            try {
                $user = User::findOrFail($id);

                if ($user->remember_token !== $verification_token) {
                    return response()->json(['message' => 'Invalid verification token.'], 400);
                }

                $user->email_verified_at = Carbon::now();
                $user->save();

                return response()->json(['message' => 'Email successfully verified.']);
            } catch (\Exception $e) {
                Log::error('Email verification failed: ' . $e->getMessage());
                return response()->json(['message' => 'Email verification failed.'], 500);
            }
        } else {
            // This block is from the existing code
            $request = app('request');
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
    }

    // This method combines the logic from both the new and existing code
    public function verifyEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'verification_code' => 'required|string', // Changed 'token' to 'verification_code' to match the new code
        ]);

        if ($validator->fails()) {
            // Customizing the error messages
            $errors = $validator->errors();
            $customMessages = [];
            if ($errors->has('email')) {
                $customMessages['email'] = "Invalid email format.";
            }
            if ($errors->has('verification_code')) { // Changed 'token' to 'verification_code' to match the new code
                $customMessages['verification_code'] = "Invalid verification code.";
            }
            return response()->json($customMessages, 422);
        }

        try {
            $user = User::where('email', $request->input('email'))->first();

            if (!$user) {
                return response()->json(['message' => 'Email not found.'], 404);
            }

            // The following block is a combination of the new and existing code
            if ($user->verification_token === $request->input('verification_code')) {
                $user->email_verified_at = Carbon::now();
                $user->save();

                return response()->json(['message' => 'Email verified successfully.'], 200);
            } else {
                // Assuming the token is stored in a password_reset_tokens table
                $tokenRecord = $user->passwordResetTokens()->where('token', $request->input('verification_code'))->first();

                if (!$tokenRecord || $tokenRecord->created_at->addMinutes(config('auth.passwords.users.expire'))->isPast()) {
                    return response()->json(['message' => 'Invalid or expired verification token.'], 400);
                }

                $user->email_verified_at = Carbon::now();
                // Clear the verification token
                $tokenRecord->delete();
                $user->save();

                return response()->json(['status' => 200, 'message' => 'Email verified successfully.'], 200);
            }
        } catch (\Exception $e) {
            Log::error('Email verification error: ' . $e->getMessage());
            return response()->json(['message' => 'An unexpected error occurred.'], 500);
        }
    }
}
