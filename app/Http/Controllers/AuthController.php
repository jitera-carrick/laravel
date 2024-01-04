<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    // ... other methods ...

    /**
     * Handle user login.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
            'recaptcha' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Placeholder for recaptcha validation logic
        // This should be replaced with actual recaptcha validation logic
        $recaptchaIsValid = true; // Replace with actual recaptcha validation logic
        if (!$recaptchaIsValid) {
            return response()->json(['message' => 'Invalid recaptcha.'], 401);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'Email address not found.'], 400);
        }

        if (!Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Incorrect password.'], 401);
        }

        // Generate session token and update user
        // This is a placeholder for actual token generation logic, such as using Laravel Sanctum or Passport
        $user->session_token = 'generated_session_token'; // Replace with actual token generation logic
        $user->is_logged_in = true;
        $user->session_expiration = now()->addHours(2); // Set session expiration for 2 hours from now
        $user->save();

        return response()->json([
            'status' => 200,
            'message' => 'Login successful.',
            'session_token' => $user->session_token,
        ]);
    }

    /**
     * Handle user logout.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        $sessionToken = $request->input('session_token');
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
            'status' => 400,
            'message' => 'Logout failed. Invalid session token.'
        ]);
    }
}
