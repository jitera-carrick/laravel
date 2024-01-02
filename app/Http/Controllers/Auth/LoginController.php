<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;
use App\Models\LoginAttempt;
use App\Models\User;
use App\Models\Session;
use App\Services\RecaptchaService; // Import the RecaptchaService

class LoginController extends Controller
{
    public function login(Request $request)
    {
        // ... existing login method code
    }

    public function logout(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'session_token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400); // Changed from 422 to 400 to match the requirement
        }

        $sessionToken = $request->input('session_token');
        $session = Session::where('session_token', $sessionToken)
                          ->where('is_active', true)
                          ->first();

        if (!$session) {
            return response()->json(['error' => 'Unauthorized - Invalid or inactive session.'], 401);
        }

        $session->is_active = false;
        $session->save();

        $user = $session->user; // Assuming there is a relationship defined in the Session model to get the user
        if ($user) {
            $user->session_token = null;
            $user->save();
        }

        // Clear the session token cookie if you are using cookie-based session management
        Cookie::queue(Cookie::forget('session_token'));

        return response()->json([
            'status' => 200,
            'message' => 'You have been successfully logged out.'
        ]);
    }
}
