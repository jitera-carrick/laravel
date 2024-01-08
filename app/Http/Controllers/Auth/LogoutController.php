
<?php

use App\Http\Requests\SessionRequest;
use App\Http\Responses\ApiResponse;
use App\Models\Session;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class LogoutController extends Controller
{
    public function __invoke(Request $request)
    {
        // ... existing code ...
    }

    public function logout(SessionRequest $request): JsonResponse
    {
        $sessionToken = $request->input('session_token');

        try {
            $session = Session::where('session_token', $sessionToken)->first();

            if (!$session) {
                return ApiResponse::error('Session not found.', 404);
            }

            $session->deactivateSession($sessionToken);

            return ApiResponse::success('User has been logged out successfully.');
        } catch (\Exception $e) {
            return ApiResponse::error('An error occurred during logout.', 500);
        }
    }

    // ... other methods ...
}
