
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Requests\ResetPasswordRequest;
use App\Http\Requests\ValidateResetTokenRequest; // Existing import
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\PasswordResetRequest;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Notifications\ResetPasswordNotification; // Existing import
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Carbon;
use App\Models\EmailLog; // New import
use App\Models\PasswordResetToken; // Existing import
use App\Models\PasswordReset; // Correct model name for password resets
use App\Http\Resources\SuccessResource; // Existing import
use App\Http\Resources\ErrorResource; // Existing import

class ForgotPasswordController extends Controller
{
    // ... (other methods)

    public function validateResetToken($request) // Modified to accept both types of requests
    {
        // Determine if the request is using the custom request validation or the default one
        if ($request instanceof ValidateResetTokenRequest) {
            $validatedData = $request->validated();
            $email = $validatedData['email'];
            $token = $validatedData['token'];
        } else {
            $request->validate([
                'email' => 'required|email',
                'token' => 'required|string',
            ]);
            $email = $request->email;
            $token = $request->token;
        }

        try {
            $tokenEntry = PasswordResetToken::where('email', $email)
                ->where('token', $token)
                ->where('used', false)
                ->first();

            if (!$tokenEntry) {
                return new ErrorResource(['error' => 'Token not found or already used']);
            }

            $tokenLifetime = Config::get('auth.passwords.users.expire') * 60;
            $tokenCreatedAt = Carbon::parse($tokenEntry->created_at);
            $tokenExpired = $tokenCreatedAt->addSeconds($tokenLifetime)->isPast();

            if ($tokenExpired) {
                return new ErrorResource(['error' => 'Token is expired']);
            }

            return new SuccessResource(['message' => 'Token is valid']);
        } catch (\Exception $e) {
            return new ErrorResource(['error' => 'An error occurred while validating the token']);
        }
    }

    public function sendResetLinkEmail(ResetPasswordRequest $request)
    {
        $email = $request->input('email');
        $user = User::where('email', $email)->first();

        if (!$user) {
            return response()->json(['message' => 'Email not registered'], 404);
        }

        $token = Str::random(60);
        $passwordResetRequest = PasswordResetRequest::create([
            'email' => $user->email,
            'token' => $token,
            'created_at' => now(),
            'expires_at' => Carbon::now()->addMinutes(Config::get('auth.passwords.users.expire')),
            'used' => false,
            'user_id' => $user->id,
        ]);

        $user->save();

        // Send the password reset notification
        $user->notify(new ResetPasswordNotification($token, $user->email));

        return response()->json(['message' => 'Password reset link sent'], 200);
    }

    // ... (other methods)

    // Added sendEmail method from new code
    protected function sendEmail($to, $subject, $view, $data)
    {
        Mail::send($view, $data, function ($message) use ($to, $subject) {
            $message->to($to)->subject($subject);
        });
    }
}
