
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AuthController extends Controller
{
    // ... other methods

    public function logout(Request $request)
    {
        try {
            $sessionToken = $request->header('session_token') ?? $request->input('session_token');
            $user = Auth::guard('web')->user();

            if ($user && $user->session_token === $sessionToken) {
                $user->update([
                    'session_token' => null,
                    'is_logged_in' => false,
                    'session_expiration' => Carbon::now(),
                ]);

                return response()->json(['message' => 'Logout successful.'], 200);
            }

            return response()->json(['error' => 'Logout failed. Session token mismatch.'], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred during logout.', 'exception' => $e->getMessage()], 500);
        }
    }
}
