<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\PasswordResetToken;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends Controller
{
    // ... (other methods)

    public function validateResetToken(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required|string',
        ]);

        try {
            $tokenEntry = PasswordResetToken::where('email', $request->email)
                ->where('token', $request->token)
                ->where('used', false)
                ->first();

            if (!$tokenEntry) {
                return response()->json(['error' => 'Token not found or already used'], 404);
            }

            $tokenLifetime = Config::get('auth.passwords.users.expire') * 60;
            $tokenCreatedAt = Carbon::parse($tokenEntry->created_at);
            $tokenExpired = $tokenCreatedAt->addSeconds($tokenLifetime)->isPast();

            if ($tokenExpired) {
                return response()->json(['error' => 'Token is expired'], 410);
            }

            return response()->json(['message' => 'Token is valid'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while validating the token'], 500);
        }
    }

    public function sendResetLinkEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            if ($errors->has('email')) {
                if ($errors->first('email') === 'The selected email is invalid.') {
                    return response()->json([
                        'status' => 422,
                        'message' => 'Email address not found.'
                    ], 422);
                }
                return response()->json([
                    'status' => 400,
                    'message' => 'Invalid email address format.'
                ], 400);
            }
        }

        $user = User::where('email', $request->email)->first();

        if ($user) {
            do {
                $token = Str::random(60);
                $tokenExists = PasswordResetToken::where('token', $token)->exists();
            } while ($tokenExists);

            $passwordResetToken = new PasswordResetToken([
                'email' => $user->email,
                'token' => $token,
                'created_at' => now(),
                'expires_at' => now()->addMinutes(Config::get('auth.passwords.users.expire')),
                'used' => false,
                'user_id' => $user->id,
            ]);
            $passwordResetToken->save();

            // Send the password reset notification
            $user->notify(new ResetPasswordNotification($token));

            return response()->json([
                'status' => 200,
                'message' => 'Reset link has been sent to your email address.'
            ], 200);
        }

        return response()->json([
            'status' => 500,
            'message' => 'An error occurred while sending the reset link.'
        ], 500);
    }

    // ... (other methods)
}
