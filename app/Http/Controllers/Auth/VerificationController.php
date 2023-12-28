<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\PasswordResetToken;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class VerificationController extends Controller
{
    // This method is no longer needed as the new requirement specifies a different endpoint and logic.
    // public function verify($user_id, $remember_token)
    // {
    //     ...
    // }

    public function verifyEmail($token)
    {
        try {
            $passwordResetToken = PasswordResetToken::where('token', $token)->first();

            if (!$passwordResetToken || $passwordResetToken->expires_at < Carbon::now()) {
                return response()->json(['message' => 'Invalid or expired email verification token.'], 404);
            }

            $user = $passwordResetToken->user;
            if (!$user) {
                return response()->json(['message' => 'User not found.'], 404);
            }

            $user->email_verified_at = Carbon::now();
            $user->save();

            $passwordResetToken->delete();

            return response()->json(['status' => 200, 'message' => 'Email verified successfully.'], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'User not found.'], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Verification failed.'], 500);
        }
    }
}
