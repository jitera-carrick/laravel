<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    // ... other methods ...

    public function logout(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'session_token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $sessionToken = $request->input('session_token');
        $user = User::where('session_token', $sessionToken)->first();

        if (!$user) {
            return response()->json(['message' => 'Invalid session token.'], 401);
        }

        $user->session_token = null;
        $user->is_logged_in = false; // Ensure the user is marked as logged out
        $user->save();

        return response()->json(['status' => 200, 'message' => 'Logout successful.']);
    }

    // ... rest of the controller ...
}
