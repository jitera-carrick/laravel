<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Session;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    // ... other methods ...

    /**
     * Handle the logout process by invalidating the user's session.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'session_token' => 'required|string',
        ]);

        // If validation fails, return a 422 Unprocessable Entity response
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        // Retrieve the session token from the request
        $sessionToken = $request->input('session_token');

        // Find the session using the provided session token
        $session = Session::where('session_token', $sessionToken)->first();

        if ($session) {
            // Invalidate the session by setting 'is_active' to false or deleting it
            $session->is_active = false;
            $session->save();

            // Return a successful logout response
            return response()->json(['status' => 200, 'message' => 'Logout successful.'], 200);
        }

        // If the session token is invalid or not found, return an unauthorized response
        return response()->json(['message' => 'Invalid session token.'], 401);
    }

    // ... other methods ...
}
