
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LogoutRequest;
use App\Services\SessionService;
use Illuminate\Http\JsonResponse;

class LogoutController extends Controller
{
    protected $sessionService;

    public function __construct(SessionService $sessionService)
    {
        $this->sessionService = $sessionService;
    }

    public function logout(LogoutRequest $request): JsonResponse
    {
        $sessionToken = $request->input('session_token');

        if ($this->sessionService->deleteSession($sessionToken)) {
            return response()->json(['message' => 'User has been logged out successfully.'], 200);
        }

        return response()->json(['message' => 'Session not found or could not be deleted.'], 404);
    }
}
