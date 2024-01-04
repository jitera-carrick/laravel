
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
