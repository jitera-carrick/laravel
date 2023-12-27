<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class LoginController extends Controller
{
    // ... Other methods in the LoginController

    /**
     * Maintain the user session based on the user_id and session_token.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function maintainUserSession(Request $request)
    {
        // Validate the request to ensure user_id is present
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'session_token' => 'sometimes|exists:sessions,session_token'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        try {
            // Retrieve the session using the Session model and the provided user_id
            $query = Session::where('user_id', $request->user_id);
            if ($request->has('session_token')) {
                $query->where('session_token', $request->session_token);
            }
            $session = $query->first();

            if (!$session) {
                return response()->json(['message' => 'Session not found.'], 404);
            }

            if ($session->expires_at->isPast()) {
                return response()->json(['message' => 'Session has expired.'], 401);
            }

            // Calculate the new expiration date based on the keep_session attribute
            $newExpirationDate = $session->keep_session ? Carbon::now()->addDays(90) : Carbon::now()->addHours(24);

            // Update the expires_at field in the session record
            $session->expires_at = $newExpirationDate;
            $session->save();

            return response()->json([
                'status' => 200,
                'message' => 'User session has been maintained.',
                'new_expiration' => $newExpirationDate->toIso8601String()
            ]);
        } catch (\Exception $e) {
            Log::error('Error maintaining user session: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred while maintaining the session.'], 500);
        }
    }

    // ... Rest of the existing code in the LoginController
}
