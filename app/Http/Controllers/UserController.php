<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Requests\CreateHairStylistRequest;
use App\Http\Requests\UpdateHairStylistRequest;
use App\Http\Requests\DeleteImageRequest;
use App\Http\Requests\UpdateUserProfileRequest;
use App\Http\Requests\EditUserProfileRequest; // Import the edit user profile request validation class
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
use App\Services\UserProfileService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Http\Resources\SuccessResource;

class UserController extends Controller
{
    // ... other methods ...

    // Existing methods remain unchanged

    // ... other methods ...

    /**
     * Edit user profile.
     *
     * @param HttpRequest $request
     * @return JsonResponse
     */
    public function editUserProfile(HttpRequest $request): JsonResponse
    {
        // Authenticate the user
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        // Validate the request using a form request validation class
        $validatedData = (new EditUserProfileRequest())->validateResolved();

        // Check if the email in the request matches the authenticated user's email
        if ($validatedData['email'] !== $user->email) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        // Update the user's email if "new_email" is provided and is different from the current email
        if (isset($validatedData['new_email']) && $validatedData['new_email'] !== $user->email) {
            $user->email = $validatedData['new_email'];
        }

        // Update other profile information if provided
        if (isset($validatedData['other_profile_info'])) {
            // Assuming 'other_profile_info' is a column in the 'users' table
            $user->other_profile_info = $validatedData['other_profile_info'];
        }

        // Save the updated user profile
        $user->save();

        // Return a success response
        return response()->json([
            'status' => 200,
            'message' => 'User profile has been successfully updated.'
        ], 200);
    }

    // ... other methods ...
}
