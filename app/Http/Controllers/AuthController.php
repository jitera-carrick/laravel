<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AuthService;
use App\Http\Requests\LogoutRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    // ... (rest of the existing code)

    public function logout(LogoutRequest $request)
    {
        $sessionToken = $request->input('session_token');
        
        // Start transaction to ensure atomicity
        DB::beginTransaction();
        try {
            // Query the "users" table to find the user with the matching "session_token".
            $user = User::where('session_token', $sessionToken)->first();
            
            if ($user) {
                // If a user is found, set the "session_token" to null, "session_expiration" to a past datetime, and "is_logged_in" to false.
                $user->session_token = null;
                $user->session_expiration = now()->subMinute();
                $user->is_logged_in = false;
                $user->save();

                DB::commit(); // Commit the transaction

                return response()->json(['message' => 'User has been logged out successfully.'], 200);
            } else {
                // If no user is found with the session token, return an error response.
                DB::rollBack(); // Rollback the transaction

                return response()->json(['error' => 'Invalid session token or logout failed.'], 401);
            }
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback the transaction on any exception

            return response()->json(['error' => 'An unexpected error occurred during logout.'], 500);
        }
    }

    // ... (rest of the existing code)
}
