
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
        $sessionToken = $request->get('session_token');
        if ($this->sessionService->maintain($sessionToken)) {
            $session = $this->sessionService->getSessionByToken($sessionToken);
            return new JsonResponse(new SessionResource($session), JsonResponse::HTTP_OK);
        } else {
            return new JsonResponse(['message' => 'Unable to maintain the session.'], JsonResponse::HTTP_BAD_REQUEST);
        }
    }
}
use App\Http\Requests\SessionRequest;
use App\Models\Session;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\SessionResource;
use Illuminate\Support\Facades\Config;

class SessionController extends Controller
{
    public function maintainSession(SessionRequest $request): JsonResponse
    {
        $session = Session::where('session_token', $request->input('session_token'))->first();

        if ($session && $session->expires_at > now() && $session->is_active) {
            $session->expires_at = now()->addMinutes(Config::get('session.lifetime'));
            $session->save();

            return response()->json(new SessionResource($session));
        }

        return response()->json(['message' => 'Session is invalid or has expired.'], 401);
    }
}
