<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class VerificationController extends Controller
{
    public function verify($id, $verification_token)
    {
        try {
            $user = User::findOrFail($id);

            if ($user->remember_token !== $verification_token) {
                return response()->json(['message' => 'Invalid verification token.'], 400);
            }

            $user->email_verified_at = Carbon::now();
            $user->save();

            return response()->json(['message' => 'Email successfully verified.']);
        } catch (\Exception $e) {
            Log::error('Email verification failed: ' . $e->getMessage());
            return response()->json(['message' => 'Email verification failed.'], 500);
        }
    }
}
