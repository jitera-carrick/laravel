<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\PasswordResetToken;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator; // Import the Validator facade
use App\Notifications\PasswordResetNotification; // Import the correct notification class

class ForgotPasswordController extends Controller
{
    // ... (other methods)

    public function requestPasswordReset(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            $response = [];
            if ($errors->has('email')) {
                $response['errors']['email'] = $errors->first('email');
            }
            return response()->json($response, 400);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'Email not found.'], 404);
        }

        $token = Str::random(60);
        $expiration = now()->addMinutes(Config::get('auth.passwords.users.expire'));

        $passwordResetToken = new PasswordResetToken([
            'email' => $user->email,
            'token' => $token,
            'expires_at' => $expiration,
            'used' => false,
            'user_id' => $user->id,
        ]);
        $passwordResetToken->save();

        $user->notify(new PasswordResetNotification($token));

        return response()->json([
            'status' => 200,
            'message' => 'Password reset request sent successfully',
            'reset_token' => $token
        ], 200);
    }

    public function validateResetToken(Request $request)
    {
        // ... (existing code)
    }

    public function sendResetLinkEmail(Request $request)
    {
        // ... (existing code)
    }

    // ... (other methods)
}
