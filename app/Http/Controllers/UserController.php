<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Requests\CreateHairStylistRequest;
use App\Http\Requests\UpdateHairStylistRequest;
use App\Http\Requests\ValidateStylistRequest;
use App\Models\User;
use App\Models\Session;
use App\Models\Request as HairStylistRequest;
use App\Models\RequestArea;
use App\Models\RequestMenu;
use App\Models\RequestImage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use App\Models\StylistRequest;
use App\Events\StylistRequestSubmitted;

class UserController extends Controller
{
    // ... other methods ...

    // Method to create or update a hair stylist request
    public function createOrUpdateHairStylistRequest(HttpRequest $request): JsonResponse
    {
        // ... existing code ...
    }

    // Method to cancel a hair stylist request
    public function cancelHairStylistRequest(HttpRequest $request): JsonResponse
    {
        // ... existing code ...
    }

    // Method to update a hair stylist request
    public function updateHairStylistRequest(HttpRequest $request, $id): JsonResponse
    {
        // ... existing code ...
    }

    // Delete a specific image from a hair stylist request
    public function deleteRequestImage(int $request_id, int $image_id): JsonResponse
    {
        // ... existing code ...
    }

    // Submit a stylist request
    public function submitStylistRequest(ValidateStylistRequest $request): JsonResponse
    {
        $validatedData = $request->validated();
        $userId = $validatedData['user_id'];
        $requestTime = now();

        $stylistRequest = StylistRequest::create([
            'user_id' => $userId,
            'status' => 'pending',
            'request_time' => $requestTime,
        ]);

        event(new StylistRequestSubmitted($stylistRequest));

        // Notification logic here (assuming Notification class exists)
        // Notification::send($admins, new StylistRequestReceived($stylistRequest));

        return Response::json([
            'request_id' => $stylistRequest->id,
            'request_time' => $requestTime->toDateTimeString(),
        ]);
    }

    /**
     * Maintain a user's session preference.
     *
     * @param HttpRequest $request
     * @param int $id User ID
     * @return JsonResponse
     */
    public function maintainUserSession(HttpRequest $request, $id = null): JsonResponse
    {
        if ($id !== null && !is_numeric($id)) {
            return response()->json(['message' => 'Wrong format.'], 400);
        }

        $user = Auth::user();
        if ($id !== null && $user->id != $id) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        $sessionToken = $request->input('session_token');
        if ($sessionToken) {
            $user = User::where('session_token', $sessionToken)->first();
            if (!$user) {
                return response()->json(['message' => 'Session could not be maintained.'], 404);
            }
        }

        $keepSession = $request->input('keep_session', false);
        if ($keepSession === null) {
            $validatedData = $request->validate([
                'keep_session' => 'required|boolean',
            ]);
            $keepSession = $validatedData['keep_session'];
        }

        $currentTime = now();
        if ($id !== null || $currentTime->lessThan($user->session_expiration)) {
            $session = $user->sessions()->firstOrCreate(['user_id' => $user->id]);
            $session->is_active = $keepSession;
            $session->expires_at = $keepSession ? now()->addYear() : now()->addMinutes(config('session.lifetime'));
            $session->save();

            return response()->json([
                'status' => 200,
                'message' => 'Session preference updated successfully.',
                'session_expiration' => $session->expires_at->toIso8601String(),
            ]);
        } else {
            return response()->json(['message' => 'Session has expired.'], 401);
        }
    }

    // ... other methods ...
}
