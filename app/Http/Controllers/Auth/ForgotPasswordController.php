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
use App\Http\Responses\ApiResponse; // Import ApiResponse if not already imported

class ForgotPasswordController extends Controller
{
    // ... (other methods)

    public function validateResetToken(Request $request)
    {
        $request->validate([
            'email' => 'required|email', // Keep the email validation from the existing code
            'token' => 'required|string',
        ]);

        try {
            $tokenEntry = PasswordResetToken::where('email', $request->email) // Use email from the request as in the existing code
                ->where('token', $request->token)
                ->where('used', false)
                ->first();

            if (!$tokenEntry) {
                return ApiResponse::error('Invalid reset token.', 404); // Use ApiResponse::error for error response
            }

            $tokenLifetime = Config::get('auth.passwords.users.expire') * 60;
            $tokenCreatedAt = Carbon::parse($tokenEntry->created_at);
            $tokenExpired = $tokenCreatedAt->addSeconds($tokenLifetime)->isPast();

            if ($tokenExpired) {
                return ApiResponse::error('The reset token is expired.', 422); // Use ApiResponse::error for expired token
            }

            return ApiResponse::success('Reset token is valid.'); // Use ApiResponse::success for success response
        } catch (\Exception $e) {
            return ApiResponse::error('An error occurred while validating the token', 500); // Handle exceptions
        }
    }
    
    public function sendResetLinkEmail(Request $request)
    {
        $validatedData = $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $validatedData['email'])->first();
        
        if ($user) {
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
            
            // Update the user's password_reset_token_id
            $user->password_reset_token_id = $passwordResetToken->id;
            $user->save();
            
            // Send the password reset notification
            $user->notify(new ResetPasswordNotification($token));

            return ApiResponse::success('Password reset link has been sent to your email.'); // Use ApiResponse::success for success response
        }

        return ApiResponse::success('If your email address exists in our database, you will receive a password reset link at your email address in a few minutes.'); // Use ApiResponse::success for success response
    }

    // ... (other methods)
}
