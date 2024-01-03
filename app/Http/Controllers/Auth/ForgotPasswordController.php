<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\PasswordResetToken;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Carbon;

class ForgotPasswordController extends Controller
{
    // ... (other methods)

    public function validateResetToken(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required|string',
        ]);

        // ... (existing code for validateResetToken method)

    }

    public function sendResetLinkEmail(Request $request)
    {
        $validatedData = $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        // ... (existing code for sendResetLinkEmail method)

    }

    public function initiatePasswordReset(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first('email')], 422);
        }

        try {
            $user = User::where('email', $request->email)->first();

            if ($user) {
                $token = Str::random(60);
                $passwordResetToken = PasswordResetToken::create([
                    'email' => $user->email,
                    'token' => $token,
                    'created_at' => now(),
                    'expires_at' => now()->addMinutes(Config::get('auth.passwords.users.expire')),
                    'used' => false,
                    'user_id' => $user->id,
                ]);

                // Send the password reset email
                Mail::to($user->email)->send(new \App\Mail\ResetPasswordMail($token));

                return response()->json(['message' => 'If your email address exists in our database, you will receive a password reset email shortly.'], 200);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred while initiating the password reset.', 'errors' => $e->getMessage()], 500);
        }

        return response()->json(['message' => 'If your email address exists in our database, you will receive a password reset email shortly.'], 200);
    }

    // ... (other methods)
}
