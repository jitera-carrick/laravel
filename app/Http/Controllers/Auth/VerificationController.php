<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class VerificationController extends Controller
{
    public function verify(Request $request)
    {
        $email = $request->input('email');
        $rememberToken = $request->input('remember_token');

        $user = User::where('email', $email)->first();

        if (!$user) {
            return response()->json(['error' => 'User not found.'], 404);
        }

        if ($rememberToken !== $user->remember_token) {
            return response()->json(['error' => 'Invalid token.'], 401);
        }

        $user->email_verified_at = Carbon::now();
        $user->remember_token = null;
        $user->save();

        return response()->json(['message' => 'Email has been successfully verified.'], 200);
    }
}
