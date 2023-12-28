<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\PasswordResetToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Mail\PasswordResetMailable; // Assuming this Mailable exists
use Illuminate\Support\Facades\Validator;

class ForgotPasswordController extends Controller
{
    public function sendResetLinkEmail(Request $request)
    {
        // Validate the email parameter
        $validator = Validator::make($request->all(), [
            'email' => 'required|email'
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Invalid email format.'], 400);
        }

        // Check if the user exists
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json(['message' => 'Email not found.'], 404);
        }

        // Generate a password reset token and save it
        $token = Str::random(60);
        $passwordResetToken = new PasswordResetToken([
            'email' => $user->email,
            'token' => $token,
            'expires_at' => now()->addHours(24),
            'used' => false,
            'user_id' => $user->id
        ]);
        $passwordResetToken->save();

        // Send the password reset email
        Mail::to($user->email)->send(new PasswordResetMailable($token));

        // Return a success response
        return response()->json([
            'status' => 200,
            'message' => 'Password reset link has been sent to your email.'
        ]);
    }
}
