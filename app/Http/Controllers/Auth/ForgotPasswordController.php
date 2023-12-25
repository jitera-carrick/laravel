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
        $createdAt = Carbon::now(); // Updated to include created_at
        $expiresAt = Carbon::now()->addHours(24); // Updated to 24 hours from creation

        $passwordReset = PasswordResetRequest::create([
            'user_id' => $user->id,
            'reset_token' => $resetToken,
            'created_at' => $createdAt, // Added created_at field
            'expires_at' => $expiresAt,
        ]);

        // Assuming Mail facade and email view already set up and configured
        Mail::send('emails.password_reset', ['token' => $resetToken], function ($message) use ($user) {
            $message->to($user->email);
            $message->subject('Password Reset Request');
        });

        return response()->json([
            'reset_token' => $resetToken,
            'expires_at' => $expiresAt,
        ]);
    }
}
