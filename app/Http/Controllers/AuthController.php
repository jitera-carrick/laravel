
<?php

use App\Http\Requests\LogoutRequest;
use App\Services\AuthService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    // Existing methods

    public function logout(LogoutRequest $request)
    {
        $sessionToken = $request->header('session_token') ?? $request->input('session_token');

        $user = AuthService::findBySessionToken($sessionToken);

        if (!$user) {
            return ApiResponse::error('Logout failed. User not found.', 404);
        }

        $user->session_token = null;
        $user->is_logged_in = false;
        $user->session_expiration = now();
        $user->save();

        return ApiResponse::success('Logout successful.');
    }

    // Other existing methods
}
