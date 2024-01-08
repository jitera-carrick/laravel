
<?php

namespace App\Http\Controllers;

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
