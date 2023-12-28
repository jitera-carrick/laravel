<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\PasswordResetToken;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;

class ForgotPasswordController extends Controller
{
    // ... (other methods in the controller)

    // Existing method for verifying reset token
    public function verifyResetToken(Request $request)
    {
        try {
            $email = $request->input('email');
            $token = $request->input('token');

            $passwordResetToken = PasswordResetToken::where('email', $email)
                ->where('token', $token)
                ->where('used', false)
                ->where('expires_at', '>', now())
                ->first();

            if ($passwordResetToken) {
                return Response::json([
                    'status' => 'success',
                    'message' => 'Token is valid. You can proceed to reset your password.'
                ]);
            } else {
                return Response::json([
                    'status' => 'error',
                    'message' => 'Invalid or expired token.'
                ], 400);
            }
        } catch (\Exception $e) {
            return Response::json([
                'status' => 'error',
                'message' => 'An error occurred while verifying the token.'
            ], 500);
        }
    }

    // Updated method for generating reset token
    public function generateResetToken(Request $request)
    {
        try {
            $request->validate(['email' => 'required|email']);

            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return Response::json([
                    'status' => 'error',
                    'message' => 'Email address not found.'
                ], 404);
            }

            $token = Str::random(60);

            $passwordResetToken = new PasswordResetToken([
                'email' => $user->email,
                'token' => $token,
                'expires_at' => now()->addHours(24),
                'used' => false,
                'user_id' => $user->id
            ]);

            $passwordResetToken->save();

            Mail::send('emails.password_reset', ['token' => $token], function ($message) use ($user) {
                $message->to($user->email);
                $message->subject('Your Password Reset Token');
            });

            return Response::json([
                'status' => 'success',
                'message' => 'A password reset token has been sent to your email address.'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return Response::json([
                'status' => 'error',
                'message' => 'Invalid email address.',
                'errors' => $e->errors(),
            ], 400);
        } catch (\Exception $e) {
            return Response::json([
                'status' => 'error',
                'message' => 'An error occurred while generating the password reset token.'
            ], 500);
        }
    }

    // ... (rest of the code in the controller)
}
