
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
use App\Models\PasswordResetRequest; // Import the PasswordResetRequest model

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
        $validatedData = $request->validate([ // Validate the email field
            'email' => 'required|email',
        ]);

        $user = User::where('email', $validatedData['email'])->first();

        if ($user) {
            $token = Str::random(60);
            $passwordResetToken = new PasswordResetToken([
                'email' => $user->email, // Use the user's email for the token
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

            return response()->json(['message' => 'Password reset link has been sent to your email.'], 200); // Return success message
        }

        return response()->json(['message' => 'If your email address exists in our database, you will receive a password reset link at your email address in a few minutes.'], 200);
    }

    // ... (other methods)
    
    // New method to handle password reset request flow
    public function handlePasswordResetRequest(Request $request)
    {
        $validatedData = $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $validatedData['email'])->first();

        if ($user) {
            $resetToken = Str::random(60);
            $requestTime = Carbon::now();

            $passwordResetRequest = new PasswordResetRequest([
                'user_id' => $user->id,
                'reset_token' => $resetToken,
                'request_time' => $requestTime,
                'status' => 'pending',
            ]);
            $passwordResetRequest->save();

            $user->notify(new ResetPasswordNotification($resetToken));

            return response()->json(['reset_token' => $resetToken, 'request_time' => $requestTime], 200);
        }

        return response()->json(['message' => 'User not found'], 404);
    }
}
