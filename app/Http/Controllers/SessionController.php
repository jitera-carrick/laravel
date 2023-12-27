<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Session;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class SessionController extends Controller
{
    /**
     * Maintain the user session based on the user_id and optional session_token.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function maintainSession(Request $request)
    {
        // Validate the request to ensure user_id is present and session_token is optional
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'session_token' => 'nullable|exists:sessions,session_token',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        try {
            // Retrieve the session using the Session model and the provided user_id and session_token
            $query = Session::where('user_id', $request->user_id);
            if ($request->has('session_token')) {
                $query->where('session_token', $request->session_token);
            }
            $session = $query->firstOrFail();

            // Determine the new expiration date
            $extensionPeriod = $session->keep_session ? 90 : 1;
            $newExpirationDate = Carbon::now()->addDays($extensionPeriod);

            // Update the session expiration
            $session->expires_at = $newExpirationDate;
            $session->save();

            // Return success response
            return response()->json([
                'message' => 'User session has been maintained.',
                'session_expiration' => $newExpirationDate->toIso8601String(),
            ]);
        } catch (\Exception $e) {
            // Handle exceptions and log errors
            Log::error('Error maintaining session: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred while maintaining the session.'], 500);
        }
    }

    // ... Rest of the existing code in the SessionController
}
