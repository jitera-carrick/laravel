<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use App\Models\PasswordResetRequest;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;

class ForgotPasswordController extends Controller
{
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'User does not exist.'], 404);
        }

        $resetToken = Str::random(60);
        $createdAt = Carbon::now();
        $expiresAt = Carbon::now()->addHours(24);

        $passwordReset = PasswordResetRequest::create([
            'user_id' => $user->id,
            'reset_token' => $resetToken,
            'created_at' => $createdAt,
            'expires_at' => $expiresAt,
        ]);

        Mail::send('emails.password_reset', ['token' => $resetToken], function ($message) use ($user) {
            $message->to($user->email);
            $message->subject('Password Reset Request');
        });

        return response()->json([
            'message' => 'Password reset email has been sent.',
            'reset_token' => $resetToken,
            'expires_at' => $expiresAt->toDateTimeString(),
        ]);
    }
}
