<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\LoginAttempt;
use App\Models\User;
use App\Models\Session;
use App\Services\RecaptchaService;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        // ... existing login method ...
    }

    public function logout(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'session_token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $sessionToken = $request->input('session_token');

        $session = Session::where('session_token', $sessionToken)
                          ->where('is_active', true)
                          ->first();

        if ($session) {
            $user = $session->user;
            $user->is_logged_in = false;
            $user->save();

            $session->is_active = false;
            $session->save();

            Cookie::queue(Cookie::forget('session_token'));

            return response()->json([
                'status' => 200,
                'message' => 'Logout successful.'
            ]);
        } else {
            $user = User::where('session_token', $sessionToken)->first();

            if ($user) {
                $user->is_logged_in = false;
                $user->session_token = null;
                $user->session_expiration = null;
                $user->save();

                return response()->json([
                    'status' => 200,
                    'message' => 'Logout successful.'
                ]);
            }

            return response()->json([
                'status' => 401,
                'message' => 'Invalid session token.'
            ], 401);
        }
    }
}
