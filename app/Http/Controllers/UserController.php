<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Requests\CreateHairStylistRequest;
use App\Http\Requests\UpdateHairStylistRequest;
use App\Http\Requests\ValidateStylistRequest;
use App\Models\User;
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

    /**
     * Delete a specific image from a hair stylist request.
     *
     * @param int $request_id The ID of the hair stylist request.
     * @param int $image_id The ID of the image to delete.
     * @return JsonResponse
     */
    public function deleteRequestImage(int $request_id, int $image_id): JsonResponse
    {
        // ... existing code ...
    }

    /**
     * Submit a stylist request.
     *
     * @param ValidateStylistRequest $request
     * @return JsonResponse
     */
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

    public function maintainUserSession(HttpRequest $request)
    {
        $sessionToken = $request->input('session_token');
        $keepSession = $request->input('keep_session', false);

        $user = User::where('session_token', $sessionToken)->first();

        if (!$user) {
            return response()->json(['message' => 'Session could not be maintained.'], 404);
        }

        $currentTime = now();
        if ($currentTime->lessThan($user->session_expiration)) {
            $newExpiration = $keepSession ? $currentTime->addDays(90) : $currentTime->addHours(24);
            $user->session_expiration = $newExpiration;
            $user->save();

            return response()->json(['session_expiration' => $user->session_expiration]);
        } else {
            return response()->json(['message' => 'Session has expired.'], 401);
        }
    }

    // ... other methods ...
}
