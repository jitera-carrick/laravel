<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\PasswordResetToken;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail; // Import the Mail facade
use App\Utils\ApiResponse; // Import the ApiResponse utility class

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
        // Retrieve the email from the request body
        $email = $request->input('email');

        // Validate the email format and existence in the "users" table
        $validatedData = $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        DB::beginTransaction();
        try {
            // Check if the email exists in the "users" table
            $user = User::where('email', $validatedData['email'])->first();

            if (!$user) {
                DB::rollBack();
                return ApiResponse::error('Email address not found.', 400);
            }

            // Generate a unique reset token
            $token = Str::random(60);

            // Store the reset token in the "password_reset_tokens" table
            $passwordResetToken = new PasswordResetToken([
                'email' => $user->email,
                'token' => $token,
                'created_at' => now(),
                'expires_at' => now()->addMinutes(Config::get('auth.passwords.users.expire')),
                'used' => false,
                'user_id' => $user->id,
            ]);
            $passwordResetToken->save();

            // Send an email to the user with the reset token link
            Mail::send('emails.password_reset', ['token' => $token], function ($message) use ($user) {
                $message->to($user->email);
                $message->subject('Password Reset Link');
            });

            DB::commit();

            // Return a success response using ApiResponse utility class
            return ApiResponse::success('Reset link sent to your email address.', 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error('An error occurred while processing your request', 500);
        }
    }

    // ... (other methods)
}
