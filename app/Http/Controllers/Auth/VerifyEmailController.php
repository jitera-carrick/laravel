<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\EmailVerificationToken;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Validation\ValidationException;

class VerifyEmailController extends Controller
{
    public function verify(Request $request)
    {
        // Attempt to use the token from the route first
        $token = $request->route('token');

        // If the token is not available in the route, use the token from the request input
        if (!$token) {
            $token = $request->input('token');
        }

        // Attempt to find the verification token in the new table
        $verificationToken = EmailVerificationToken::where('token', $token)
                            ->where('expires_at', '>', Carbon::now())
                            ->first();

        // If the verification token is not found in the new table, check the old method
        if (!$verificationToken) {
            $user_id = $request->input('user_id');
            $remember_token = $request->input('remember_token');

            $user = User::find($user_id);

            if (!$user) {
                return response()->json(['message' => 'User not found.'], 404);
            }

            if ($user->remember_token !== $remember_token) {
                throw new ValidationException("Invalid token provided.");
            }

            // Retrieve token expiration time from the auth config
            $tokenLifetime = Config::get('auth.passwords.users.expire');

            // Check if the token has expired
            $tokenCreatedAt = Carbon::parse($user->passwordResetTokens()->where('token', $token)->first()->created_at ?? null);
            if (Carbon::now()->diffInMinutes($tokenCreatedAt) > $tokenLifetime) {
                throw ValidationException::withMessages(['token' => 'The verification link has expired.']);
            }

            // Update the user's email verification status
            $user->email_verified_at = Carbon::now();
            $user->remember_token = null; // Clear the verification token
            $user->updated_at = Carbon::now();
            $user->save();

            return response()->json(['message' => 'Email verified successfully.'], 200);
        }

        // If the verification token is found in the new table
        $user = $verificationToken->user;

        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        // Update the user's email verification status
        $user->email_verified_at = Carbon::now();
        $user->remember_token = null; // Clear the verification token
        $user->save();

        // Mark the token as used (no need to save token usage for this task, so this line is commented out)
        // $verificationToken->used = true;
        // $verificationToken->save();

        return response()->json(['message' => 'Email verified successfully.'], 200);
    }
}
