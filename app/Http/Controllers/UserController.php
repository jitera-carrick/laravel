<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Requests\CreateHairStylistRequest;
use App\Http\Requests\UpdateHairStylistRequest;
use App\Http\Requests\DeleteImageRequest;
use App\Http\Requests\UpdateUserProfileRequest;
use App\Models\User;
use App\Models\Request as HairStylistRequest;
use App\Models\RequestArea;
use App\Models\RequestMenu;
use App\Models\RequestImage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request as HttpRequest;
use App\Services\RequestService;
use App\Services\ImageService;
use App\Services\UserService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Http\Resources\SuccessResource;
use App\Http\Resources\ErrorResource; // Import the ErrorResource
use App\Mail\UserProfileUpdated;

class UserController extends Controller
{
    // ... other methods ...

    // Existing methods remain unchanged

    // Method to create or update a hair stylist request
    // ... existing createOrUpdateHairStylistRequest method ...

    // Method to cancel a hair stylist request
    // ... existing cancelHairStylistRequest method ...

    // Method to update a hair stylist request
    // ... existing updateHairStylistRequest method ...

    // Method to delete a specific image from a hair stylist request
    // ... existing deleteRequestImage method ...

    // Method to update user profile
    public function updateUserProfile(UpdateUserProfileRequest $request): JsonResponse
    {
        $userService = new UserService();

        try {
            $updatedUser = $userService->updateUserProfile($request->user()->id, $request->validated());

            if ($updatedUser) {
                Mail::to($updatedUser->email)->send(new UserProfileUpdated($updatedUser));
                return (new SuccessResource(['message' => 'Profile updated successfully.']))->response();
            } else {
                return (new ErrorResource(['message' => 'No changes detected or update failed.', 'status_code' => 422]))->response()->setStatusCode(422);
            }
        } catch (\Exception $e) {
            if ($e->getCode() === 409) {
                return (new ErrorResource(['message' => 'Email already registered.', 'status_code' => 409]))->response()->setStatusCode(409);
            }
            return (new ErrorResource(['message' => $e->getMessage(), 'status_code' => 500]))->response()->setStatusCode(500);
        }
    }

    // ... other methods ...
}
