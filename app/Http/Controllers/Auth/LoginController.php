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
        // ... existing login method code ...
    }

    public function logout(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'session_token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        try {
            $sessionToken = $request->input('session_token');
            $session = Session::where('session_token', $sessionToken)
                              ->where('is_active', true)
                              ->first();

            if ($session) {
                $session->is_active = false;
                $session->save();

                Cookie::queue(Cookie::forget('session_token'));

                return response()->json([
                    'status' => 200,
                    'message' => 'You have been logged out successfully.'
                ]);
            } else {
                return response()->json([
                    'status' => 401,
                    'message' => 'Invalid session token.'
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'An error occurred during logout.',
                'error' => $e->getMessage()
            ]);
        }
    }
}
