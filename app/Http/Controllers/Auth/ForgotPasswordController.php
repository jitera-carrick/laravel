<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\PasswordResetToken;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Support\Facades\Config;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon; // Ensure Carbon is imported for date calculations

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

    public function sendResetLinkEmail(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);
        
        if ($validator->fails()) {
            $errorMessage = $validator->errors()->first('email');
            if (str_contains($errorMessage, 'exists')) {
                return response()->json(['message' => 'Email address not found.'], 404);
            }
            return response()->json(['message' => 'Invalid email address format.'], 400);
        }

        $user = User::where('email', $request->input('email'))->first();
        if (!$user) {
            return response()->json(['message' => 'Email address not found.'], 404);
        }

        $token = Str::random(64);
        $passwordResetToken = new PasswordResetToken([
            'email' => $user->email,
            'token' => $token,
            'expires_at' => now()->addMinutes(Config::get('auth.passwords.users.expire')),
            'used' => false,
            'user_id' => $user->id,
        ]);
        $passwordResetToken->save();

        $user->notify(new ResetPasswordNotification($token));

        return response()->json(['status' => 'success', 'message' => 'Reset link has been sent to your email address.'], 200);
    }

    // ... (other methods)
}
