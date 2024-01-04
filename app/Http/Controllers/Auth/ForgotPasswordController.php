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
use Illuminate\Support\Facades\Password; // Import the Password facade
use Illuminate\Http\JsonResponse; // Import JsonResponse

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
        $validator = \Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['message' => 'Please enter a valid email address.'], 400);
        }

        $user = User::where('email', $request->input('email'))->first();
        if (is_null($user)) {
            return response()->json(['message' => 'The email address does not exist in our records.'], 404);
        }

        $token = Str::random(64); // Updated token length for better security
        $passwordResetToken = new PasswordResetToken([
            'email' => $user->email,
            'token' => $token,
            // Use the 'expires_at' field from the new code as it is more precise
            'expires_at' => now()->addMinutes(Config::get('auth.passwords.users.expire')),
            'used' => false,
            'user_id' => $user->id,
        ]); // No changes needed here as the structure is already following the guideline
        $passwordResetToken->save();

        // No need to update the user's password_reset_token_id as it's not used in the new code

        // Send the password reset notification
        $user->notify(new ResetPasswordNotification($token));

        return response()->json(['status' => 'success', 'message' => 'Reset link has been sent to your email address.'], 200); // Updated status to a more descriptive string
    }

    // ... (other methods)
}
