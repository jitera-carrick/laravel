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
use App\Models\PasswordResetRequest; // Import the PasswordResetRequest model

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

    public function handlePasswordResetRequest(Request $request)
    {
        $validatedData = $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $validatedData['email'])->first();

        if ($user) {
            $resetToken = Str::random(60);
            $requestTime = Carbon::now();

            $passwordResetRequest = new PasswordResetRequest([
                'user_id' => $user->id,
                'reset_token' => $resetToken,
                'request_time' => $requestTime,
                'status' => 'pending',
            ]);
            $passwordResetRequest->save();

            $user->notify(new ResetPasswordNotification($resetToken));

            return response()->json([
                'status' => 200,
                'message' => 'Password reset request sent successfully.',
                'reset_token' => $resetToken,
                'request_time' => $requestTime, // Added from new code
            ]);
        }

        return response()->json(['message' => 'User not found'], 404);
    }

    // ... (other methods remain unchanged)
}
