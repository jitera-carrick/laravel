<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class LoginController extends Controller
{
    // ... (other methods in the LoginController)

    public function logout(Request $request)
    {
        $sessionToken = $request->input('session_token');

        // Query the "users" table to find a user with the matching "session_token".
        $user = User::where('session_token', $sessionToken)->first();

        if ($user) {
            // Invalidate the session by setting the "session_token" to null and updating the "token_expiration" to the current timestamp.
            $user->session_token = null;
            $user->token_expiration = now();
            $user->save();

            // Assuming the session token is used to retrieve the authenticated user
            // and the user is currently authenticated.
            if (Auth::guard('web')->user()->id === $user->id) {
                Auth::guard('web')->logout();
            }

            return response()->json(['logout_success' => true]);
        }

        return response()->json(['logout_success' => false], 401);
    }

    // ... (other methods in the LoginController)
}
