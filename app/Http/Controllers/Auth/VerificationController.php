
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\VerifyEmailRequest; // Added line
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Validation\ValidationException;

class VerificationController extends Controller
{
    public function verify($token)
    {
        // This method remains unchanged
        $user = User::where('remember_token', $token)->first();

        if (!$user) {
            return response()->json(['message' => 'Invalid token.'], 404);
        }

        $tokenLifetime = Config::get('auth.passwords.users.expire') * 60;
        $tokenCreationDate = Carbon::parse($user->created_at);

        if (Carbon::now()->diffInSeconds($tokenCreationDate) > $tokenLifetime) {
            return response()->json(['message' => 'Token has expired.'], 422);
        }

        if (!$user->email_verified_at) {
            $user->email_verified_at = Carbon::now();
            $user->save();
        }

        return response()->json(['message' => 'Email verified successfully.'], 200);
    }

    public function verifyEmail(VerifyEmailRequest $request)
    {
        // This is the new method added by the patch
        $token = $request->input('token');
        $user = User::where('remember_token', $token)->first();

        if (!$user) {
            return response()->json(['message' => 'Invalid token.'], 400);
        }

        $tokenLifetime = Config::get('auth.passwords.password_resets.expire') * 60; // Updated line
        $tokenCreationDate = Carbon::parse($user->created_at);

        if (Carbon::now()->diffInSeconds($tokenCreationDate) > $tokenLifetime) {
            return response()->json(['message' => 'Token has expired.'], 422);
        }

        if (!$user->email_verified_at) {
            $user->update(['email_verified_at' => Carbon::now()]); // Updated line
            $user->save();
        }

        return response()->json(['message' => 'Email verified successfully.'], 200);
    }
}
