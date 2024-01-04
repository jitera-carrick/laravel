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
        $validatedData = $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $validatedData['email'])->first();

        if (!$user) {
            return response()->json(['error' => 'The email address does not exist in our records.'], 404);
        }

        $token = Str::random(60);
        $passwordResetToken = new PasswordResetToken([
            'email' => $user->email,
            'token' => $token,
            'created_at' => now(),
            'expires_at' => now()->addMinutes(Config::get('auth.passwords.users.expire')),
            'used' => false,
            'user_id' => $user->id,
        ]);
        $passwordResetToken->save();

        try {
            Mail::send('emails.reset', ['token' => $token], function ($message) use ($user) {
                $message->to($user->email);
                $message->subject('Password Reset Link');
            });
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to send reset link email.'], 500);
        }

        return response()->json(['status' => 200, 'message' => 'Reset link has been sent to your email address.'], 200);
    }

    // ... (other methods)
}
