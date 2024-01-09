
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\SessionMaintenanceRequest;
use App\Services\SessionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use App\Models\Session;
use Illuminate\Support\Facades\Auth;

class SessionController extends Controller
{
    protected $sessionService;

    public function __construct(SessionService $sessionService)
    {
        $this->sessionService = $sessionService;
    }

    public function maintainSession(SessionMaintenanceRequest $request): JsonResponse
    {
        $sessionToken = $request->input('session_token');
        $keepSession = $request->input('keep_session');

        try {
            $userSession = $this->sessionService->findByToken($sessionToken);

            if (!$userSession) {
                return response()->json(['message' => 'Invalid session token.'], 404);
            }

            $newExpiration = $keepSession ? now()->addDays(90) : now()->addHours(24);
            $userSession->session_expiration = $newExpiration;
            $userSession->save();

            return response()->json([
                'session_maintenance_status' => 'success',
                'new_expiration' => $newExpiration->toDateTimeString(),
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred while maintaining the session.'], 500);
        }
    }

    public function logout(Request $request): JsonResponse
    {
        $sessionToken = $request->input('session_token');

        try {
            $session = Session::where('session_token', $sessionToken)->first();

            if (!$session) {
                return response()->json(['message' => 'Session not found.'], 404);
            }

            $session->invalidateSession();

            $user = $session->user;
            $user->logoutUser();

            return response()->json(['message' => 'User has been logged out successfully.']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred during logout.'], 500);
        }
    }

    public function cancelLoginProcess()
    {
        $userId = Auth::id();
        $result = $this->sessionService->cancelOngoingLogin($userId);

        if ($result) {
            return response()->json(['message' => 'Login process has been canceled successfully.'], 200);
        } else {
            return response()->json(['message' => 'No ongoing login process to cancel.'], 404);
        }
    }

    // ... other methods ...
}
