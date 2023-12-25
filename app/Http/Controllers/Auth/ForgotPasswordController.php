<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\PasswordResetRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Notification;
use App\Notifications\ResetPasswordNotification;

class ForgotPasswordController extends Controller
{
    // Add your existing methods here

    /**
     * Send a reset link to the given user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function sendResetLinkEmail(Request $request)
    {
        $validatedData = $request->validate(['email' => 'required|email']);

        $user = User::where('email', $validatedData['email'])->first();

        if (!$user) {
            return response()->json(['message' => 'User does not exist.'], 404);
        }

        $resetToken = Hash::make(Str::random(60));
        $expiresAt = now()->addHours(24);

        $passwordReset = new PasswordResetRequest([
            'user_id' => $user->id,
            'reset_token' => $resetToken,
            'requested_at' => now(),
            'expires_at' => $expiresAt,
        ]);

        $passwordReset->save();

        $user->notify(new ResetPasswordNotification($resetToken));

        return response()->json([
            'reset_token' => $resetToken,
            'requested_at' => $passwordReset->requested_at,
            'expires_at' => $passwordReset->expires_at,
        ]);
    }
}
