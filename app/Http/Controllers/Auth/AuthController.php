
<?php

use App\Http\Controllers\Controller;
use App\Services\SessionService;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    public function cancelLogin(): JsonResponse
    {
        $sessionService = new SessionService();
        $sessionService->cancelLoginProcess();

        return response()->json(['message' => 'Login process has been canceled'], 200);
    }

    // ... other methods ...
}
