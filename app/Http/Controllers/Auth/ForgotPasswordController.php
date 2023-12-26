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
        // Validate the email input
        if (empty($request->email) || !filter_var($request->email, FILTER_VALIDATE_EMAIL)) {
            return response()->json(['message' => 'Invalid email format.'], 400);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'User does not exist.'], 404);
        }

        $resetToken = Str::random(60);
        $expiresAt = Carbon::now()->addHours(24);

        // Create a new entry in the "password_reset_tokens" table with the user's email, the generated token, and the current datetime for the "created_at" column.
        $passwordReset = PasswordResetRequest::updateOrCreate(
            ['email' => $user->email], // Changed from 'user_id' to 'email' to match the requirement
            [
                'reset_token' => $resetToken,
                'expires_at' => $expiresAt,
                'created_at' => Carbon::now() // Added 'created_at' field
            ]
        );

        Mail::send('emails.password_reset', ['token' => $resetToken], function ($message) use ($user) {
            $message->to($user->email);
            $message->subject('Password Reset Request');
            // Set the 'from' address and name according to the configuration
            $message->from(config('mail.from.address'), config('mail.from.name'));
        });

        return response()->json([
            'message' => 'Password reset email has been sent.',
        ]);
    }
}
