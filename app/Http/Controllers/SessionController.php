
<?php

use App\Http\Requests\SessionRequest;
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
            return response()->json(['message' => 'Session maintained successfully.'], 200);
        }
        return response()->json(['message' => 'Session has expired or is invalid.'], 401);
    }
}
