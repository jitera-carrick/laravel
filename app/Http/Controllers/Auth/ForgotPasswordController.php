<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\PasswordResetToken;
use Illuminate\Support\Str;
use App\Notifications\ResetPasswordNotification;

class ForgotPasswordController extends Controller
{
    // ... (other methods)

    public function sendResetToken(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'Email address not found.'], 404);
        }

        $token = Str::random(60);
        $expiresAt = now()->addHours(2); // Set token expiration time as needed

        PasswordResetToken::create([
            'email' => $user->email,
            'token' => $token,
            'created_at' => now(),
            'expires_at' => $expiresAt,
            'used' => false,
            'user_id' => $user->id,
        ]);

        $user->notify(new ResetPasswordNotification($token));

        return response()->json(['message' => 'Password reset instructions have been sent to your email.']);
    }

    // ... (other methods)
}
