<?php

namespace App\Http\Controllers\Auth;

use App\Http\Requests\ResetPasswordRequest;
use App\Http\Requests\ValidateResetTokenRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\PasswordReset;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Carbon;
use App\Models\EmailLog;
use App\Models\PasswordResetToken;
use App\Http\Resources\SuccessResource;
use App\Http\Resources\ErrorResource;
use App\Http\Requests\EmailPasswordResetRequest;
use Illuminate\Http\JsonResponse;

class ForgotPasswordController extends Controller
{
    // ... (other methods)

    public function validateResetToken($request)
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

    public function sendResetLinkEmail(Request $request)
    {
        $validatedData = $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $email = $validatedData['email'];
        $user = User::where('email', $email)->first();

        if (!$user) {
            return response()->json(['message' => 'Email address not found.'], 404);
        }

        $token = Str::random(60);
        PasswordReset::create([
            'email' => $user->email,
            'token' => $token,
            'created_at' => Carbon::now(),
            'expires_at' => Carbon::now()->addMinutes(Config::get('auth.passwords.users.expire')),
            'used' => false,
            'user_id' => $user->id,
        ]);

        $user->save();

        if (method_exists($this, 'sendEmail')) {
            $resetUrl = url(config('app.url').route('password.reset', ['token' => $token, 'email' => $user->email], false));
            $this->sendEmail(
                $user->email,
                'Password Reset Link',
                'emails.reset',
                ['url' => $resetUrl]
            );

            EmailLog::create(['email_type' => 'reset_password', 'sent_at' => Carbon::now(), 'user_id' => $user->id]);
        } else {
            $user->notify(new ResetPasswordNotification($token));
        }

        return response()->json(['message' => 'Password reset link sent successfully.'], 200);
    }

    // ... (other methods)

    protected function sendEmail($to, $subject, $view, $data)
    {
        Mail::send($view, $data, function ($message) use ($to, $subject) {
            $message->to($to)->subject($subject);
        });
    }
}
