<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateUserProfileRequest;
use App\Http\Requests\UpdateHairStylistRequest; // Import the UpdateHairStylistRequest
use App\Models\User;
use App\Models\Request;
use App\Models\RequestImage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use App\Notifications\VerifyEmailNotification;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class UserController extends Controller
{
    // ... other methods ...

    // Existing methods remain unchanged

    // New method to edit a hair stylist request
    /**
     * Edit an existing hair stylist request.
     *
     * @param UpdateHairStylistRequest $request
     * @return JsonResponse
     */
    public function editHairStylistRequest(UpdateHairStylistRequest $request): JsonResponse
    {
        // ... The new method code remains unchanged ...
    }

    // Rest of the UserController methods...
    // ... other methods ...

    public function updateProfile(UpdateUserProfileRequest $request): JsonResponse
    {
        // ... The existing updateProfile method code remains unchanged ...
    }

    // New method for updating user profile information
    public function updateUserProfile(UpdateUserProfileRequest $request): JsonResponse
    {
        // ... The existing updateUserProfile method code remains unchanged ...
    }
}
