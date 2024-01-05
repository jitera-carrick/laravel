
<?php

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;

class AuthController extends Controller
{
    // ... other methods ...

    public function someMethod()
    {
        // Existing code...
    }

    public function logout(Request $request)
    {
        $sessionToken = $request->header('session_token') ?: $request->input('session_token');

        if (!$sessionToken) {
            return response()->json(['error' => 'Session token is required.'], 401);
        }

        $user = User::where('session_token', $sessionToken)->first();

        if (!$user) {
            return response()->json(['error' => 'Invalid session token.'], 401);
        }

        $user->update([
            'session_token' => null,
            'is_logged_in' => false,
            'session_expiration' => Carbon::now(),
        ]);

        return response()->json([
            'status' => 200,
            'message' => 'Logout successful.'
        ]);
    }
}
