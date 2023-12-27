<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Session;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator; // Make sure to include the Validator facade

class SessionController extends Controller
{
    /**
     * Maintain the user session based on the session_token and remember flag.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function maintainSession(Request $request)
    {
        // Validate the request to ensure session_token is present and remember is a boolean
        $validator = Validator::make($request->all(), [
            'session_token' => 'required|exists:sessions,session_token',
            'remember' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            // Determine which validation failed
            if ($validator->errors()->has('session_token')) {
                return response()->json(['message' => 'Invalid session token.'], 401);
            } elseif ($validator->errors()->has('remember')) {
                return response()->json(['message' => 'Invalid value for remember.'], 422);
            }
            // If there are other errors, return a 400 Bad Request
            return response()->json($validator->errors(), 400);
        }

        try {
            // Retrieve the session using the Session model and the provided session_token
            $session = Session::where('session_token', $request->session_token)->first();

            if (!$session) {
                return response()->json(['message' => 'Invalid session token.'], 401);
            }

            if ($request->remember) {
                // Calculate the new expiration date
                $newExpirationDate = Carbon::now()->addDays(30); // Assuming 30 days is the desired extension period

                // Update the expires_at field in the session record
                $session->expires_at = $newExpirationDate;
                $session->save();

                return response()->json([
                    'status' => 200,
                    'message' => 'Session maintained successfully.',
                    'session_expiration' => $newExpirationDate->toIso8601String(),
                ]);
            }

            return response()->json(['status' => 200, 'message' => 'Session maintained without changes.']);
        } catch (\Exception $e) {
            Log::error('Error maintaining session: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred while maintaining the session.'], 500);
        }
    }

    // ... Rest of the existing code in the SessionController
}
