
<?php

use App\Http\Requests\LogoutRequest;
use App\Http\Controllers\Controller;
use App\Services\SessionService;
use Illuminate\Http\JsonResponse;

class LogoutController extends Controller
{
    protected $sessionService;

    public function __construct(SessionService $sessionService)
    {
        $this->sessionService = $sessionService;
    }

    // ... other methods ...

    public function logout(LogoutRequest $request): JsonResponse
    {
        $sessionToken = $request->input('session_token');

        $deactivated = $this->sessionService->deactivateSession($sessionToken);

        if (!$deactivated) {
            return response()->json(['message' => 'Invalid session token or session could not be deactivated.'], 400);
        }

        return response()->json(['message' => 'You have been logged out successfully.']);
    }

    // ... other methods ...

}
