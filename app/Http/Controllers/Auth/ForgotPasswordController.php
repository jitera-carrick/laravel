<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use App\Models\PasswordResetRequest;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;

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
            ['email' => $user->email],
            [
                'reset_token' => $resetToken,
                'expires_at' => $expiresAt,
                'created_at' => Carbon::now()
            ]
        );

        Mail::send('emails.password_reset', ['token' => $resetToken], function ($message) use ($user) {
            $message->to($user->email);
            $message->subject('Password Reset Request');
            $message->from(config('mail.from.address'), config('mail.from.name'));
        });

        return response()->json([
            'message' => 'Password reset email has been sent.',
        ]);
    }

    // New method to handle password reset request API
    public function requestPasswordReset(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Invalid email format.'], 400);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'Email not found.'], 404);
        }

        $resetToken = Str::random(60);
        $expiresAt = Carbon::now()->addHours(24);

        $passwordResetRequest = new PasswordResetRequest([
            'email' => $user->email,
            'reset_token' => $resetToken,
            'created_at' => Carbon::now(),
            'expires_at' => $expiresAt,
        ]);

        try {
            $passwordResetRequest->save();

            Mail::send('emails.password_reset', ['token' => $resetToken], function ($message) use ($user) {
                $message->to($user->email);
                $message->subject('Password Reset Request');
                $message->from(config('mail.from.address'), config('mail.from.name'));
            });

            return response()->json([
                'status' => 200,
                'message' => 'Password reset request sent successfully. Please check your email.',
                'token' => $resetToken // Include the generated token in the response body
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An unexpected error occurred.'], 500);
        }
    }
}
