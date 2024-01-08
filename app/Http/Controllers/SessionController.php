
<?php

use App\Http\Requests\SessionRequest;
use App\Http\Resources\SessionResource;
use App\Services\SessionService;
use Illuminate\Http\JsonResponse;

namespace App\Http\Controllers;

class SessionController extends Controller
{
    protected $sessionService;

    public function __construct(SessionService $sessionService)
    {
        $this->sessionService = $sessionService;
    }

    public function maintainSession(SessionRequest $request): JsonResponse
    {
        $sessionToken = $request->input('session_token');
        if ($this->sessionService->maintain($sessionToken)) {
            return response()->json(['message' => 'Session successfully maintained.']);
        } else {
            return response()->json(['error' => 'Session expired, please log in again.'], 401);
        }
    }

    // ... other methods ...
}
