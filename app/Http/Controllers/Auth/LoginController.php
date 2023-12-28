<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        // Validate the request
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Check if the email is valid
        if (!filter_var($credentials['email'], FILTER_VALIDATE_EMAIL)) {
            return response()->json(['message' => 'Invalid email format.'], 400);
        }

        // Check if the user exists
        $user = User::where('email', $credentials['email'])->first();
        if (!$user) {
            return response()->json(['message' => 'Email not found.'], 401);
        }

        // Attempt to log in the user
        if (Auth::attempt($credentials)) {
            // Generate a new access token
            $token = $user->createToken('authToken')->plainTextToken;

            // Return success response with the access token
            return response()->json([
                'status' => 200,
                'message' => 'Login successful.',
                'access_token' => $token
            ], 200);
        }

        // Return error response if the login attempt fails
        return response()->json(['message' => 'Invalid password.'], 401);
    }
}
