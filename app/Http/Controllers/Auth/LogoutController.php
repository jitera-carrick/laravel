<?php

use App\Http\Requests\LogoutRequest;
use App\Http\Controllers\Controller;
use App\Services\SessionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LogoutController extends Controller
{
    protected $sessionService;

    public function __construct(SessionService $sessionService)
    {
        $this->sessionService = $sessionService;
        $this->middleware('auth'); // Ensure user is authenticated
    }

    public function logout(Request $request): JsonResponse
    {
        $sessionToken = $request->input('session_token');

        try {
            $deactivated = $this->sessionService->deactivateSession($sessionToken);

            if ($deactivated) {
                return response()->json(['message' => 'Logout successful.'], 204);
            } else {
                // Since the user needs to be authenticated to access this endpoint,
                // returning a 401 Unauthorized if the session cannot be deactivated is appropriate.
                return response()->json(['message' => 'Unauthorized.'], 401);
            }
        } catch (\Exception $e) {
            // Log the exception for debugging purposes
            // Log::error('Logout failed: ' . $e->getMessage());
            return response()->json(['message' => 'Internal Server Error.'], 500);
        }
    }

    // ... other methods ...

}
