<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateUserProfileRequest;
use App\Http\Requests\UpdateHairStylistRequest; // Import the new request validation class
use App\Models\User;
use App\Models\Request as HairStylistRequest; // Renamed to avoid confusion with HTTP Request
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use App\Notifications\VerifyEmailNotification;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    // ... other methods ...

    // Existing updateProfile method remains unchanged ...

    // Existing updateUserProfile method remains unchanged ...

    // Existing expireRequest method remains unchanged ...

    // New method to cancel a hair stylist request
    public function cancelHairStylistRequest(int $request_id): JsonResponse
    {
        try {
            $hairStylistRequest = HairStylistRequest::findOrFail($request_id);

            // Check if the authenticated user can cancel the request
            if (Auth::id() !== $hairStylistRequest->user_id) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }

            $hairStylistRequest->status = 'cancelled';
            $hairStylistRequest->save();

            return response()->json([
                'status' => 200,
                'message' => 'Request cancelled successfully.'
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Request not found.'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while cancelling the request.'
            ], 500);
        }
    }

    // ... rest of the UserController ...
}
