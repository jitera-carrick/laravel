
<?php

namespace App\Http\Controllers;

use App\Http\Requests\SessionRequest;
use App\Models\Session;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\SessionResource;
use Illuminate\Support\Facades\Config;
use App\Exceptions\SessionExpiredException;

class SessionController extends Controller
{
    public function maintainSession(SessionRequest $request): JsonResponse
    {
        $session = Session::where('session_token', $request->input('session_token'))->first();

        if ($session && $session->expires_at > now() && $session->is_active) {
            $session->touch();

            return response()->json(['message' => 'Session is valid.', 'status' => true]);
        }

        if ($session) {
            $session->is_active = false;
            $session->save();

            throw new SessionExpiredException('Session has expired and is now inactive.');
        }

        return response()->json(['message' => 'Session is invalid or has expired.', 'status' => false], 401);
    }
}
