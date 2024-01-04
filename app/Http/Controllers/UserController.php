
<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Requests\CreateHairStylistRequest; // Import the new form request validation class
use App\Http\Requests\UpdateHairStylistRequest; // Import the update form request validation class
use App\Http\Requests\ValidateStylistRequest; // Import the ValidateStylistRequest form request validation class
use App\Models\User;
use App\Models\Request as HairStylistRequest; // Renamed to avoid confusion with HTTP Request
use App\Models\RequestArea;
use App\Models\RequestMenu;
use App\Models\RequestImage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response; // Import the Response facade
use Illuminate\Support\Str;
use App\Models\StylistRequest;
use App\Events\StylistRequestSubmitted;

class UserController extends Controller
{
    // ... other methods ...

    // Existing methods remain unchanged

    // Method to create or update a hair stylist request
    public function createOrUpdateHairStylistRequest(HttpRequest $request): JsonResponse
    {
        // Authenticate the user based on the "user_id"
        $userId = $request->input('user_id');
        $user = User::find($userId);
        if (!$user || $userId != Auth::id()) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        // Use the CreateHairStylistRequest form request class if it's a POST request
        if ($request->isMethod('post')) {
            $validatedData = (new CreateHairStylistRequest())->validateResolved();
        } else {
            // Use the UpdateHairStylistRequest form request class if it's not a POST request
            $validatedData = (new UpdateHairStylistRequest())->validateResolved();
        }

        // Check if the authenticated user has an existing valid request
        $existingRequest = HairStylistRequest::where('user_id', $user->id)
                                             ->where('status', 'pending')
                                             ->first();

        if ($existingRequest) {
            // Update the existing request with the new data
            $existingRequest->update([
                'area' => $validatedData['area'],
                'menu' => $validatedData['menu'],
                'hair_concerns' => $validatedData['hair_concerns'],
            ]);
            $hairStylistRequest = $existingRequest;
        } else {
            // Create a new HairStylistRequest model instance
            $hairStylistRequest = new HairStylistRequest([
                'user_id' => $user->id,
                'area' => $validatedData['area'],
                'menu' => $validatedData['menu'],
                'hair_concerns' => $validatedData['hair_concerns'],
                'status' => 'pending', // Set the initial status to 'pending'
            ]);

            // Save the new request to the database
            $hairStylistRequest->save();
        }

        // Delete old images if updating an existing request
        if ($existingRequest) {
            $existingRequest->requestImages()->delete();
        }

        // Iterate over the "image_paths" array and create RequestImage instances
        foreach ($validatedData['image_paths'] as $imagePath) {
            $requestImage = new RequestImage([
                'request_id' => $hairStylistRequest->id,
                'image_path' => $imagePath,
            ]);
            $requestImage->save();
        }

        // Prepare the response data
        $responseData = [
            'request_id' => $hairStylistRequest->id,
            'status' => $hairStylistRequest->status,
            'message' => 'Hair stylist request registration has been successfully processed.'
        ];

        // Return the response with the newly created or updated request details
        return response()->json($responseData);
    }

    // ... other methods ...

    // Method to cancel a hair stylist request
    public function cancelHairStylistRequest(HttpRequest $request): JsonResponse
    {
        // Validate the request_id and check if it exists in the requests table
        $validatedData = $request->validate([
            'request_id' => 'required|exists:requests,id',
        ]);

        // Retrieve the HairStylistRequest model instance using the request_id
        $hairStylistRequest = HairStylistRequest::findOrFail($validatedData['request_id']);

        // Check if the authenticated user is the owner of the request
        if ($hairStylistRequest->user_id != Auth::id()) {
            return response()->json(['message' => 'Unauthorized access.'], 403);
        }

        // Update the status column of the HairStylistRequest instance to 'canceled'
        $hairStylistRequest->status = 'canceled';

        // Save the changes to the HairStylistRequest instance
        $hairStylistRequest->save();

        // Return a JsonResponse with the request_id, updated status, and a confirmation message
        return response()->json([
            'request_id' => $hairStylistRequest->id,
            'status' => $hairStylistRequest->status,
            'message' => 'Hair stylist request registration has been successfully canceled.'
        ]);
    }

    // ... other methods ...

    // Method to update a hair stylist request
    public function updateHairStylistRequest(HttpRequest $request, $id): JsonResponse
    {
        // Authenticate the user based on the "user_id"
        $userId = Auth::id();
        $hairStylistRequest = HairStylistRequest::find($id);

        if (!$hairStylistRequest) {
            return response()->json(['message' => 'Request not found.'], 404);
        }

        if ($hairStylistRequest->user_id != $userId) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        // Use the UpdateHairStylistRequest form request class for validation
        $validatedData = (new UpdateHairStylistRequest())->validateResolved();

        // Update the 'hair_concerns' if provided
        if (isset($validatedData['hair_concerns'])) {
            $hairStylistRequest->hair_concerns = $validatedData['hair_concerns'];
        }

        // Update the related 'area' and 'menu' records
        RequestArea::where('request_id', $hairStylistRequest->id)->delete();
        RequestMenu::where('request_id', $hairStylistRequest->id)->delete();

        foreach ($validatedData['area'] as $areaId) {
            RequestArea::create([
                'request_id' => $hairStylistRequest->id,
                'area_id' => $areaId,
            ]);
        }

        foreach ($validatedData['menu'] as $menuId) {
            RequestMenu::create([
                'request_id' => $hairStylistRequest->id,
                'menu_id' => $menuId,
            ]);
        }

        // Handle the 'images'
        if (isset($validatedData['images'])) {
            RequestImage::where('request_id', $hairStylistRequest->id)->delete();

            foreach ($validatedData['images'] as $image) {
                $path = $image->store('request_images', 'public');
                RequestImage::create([
                    'request_id' => $hairStylistRequest->id,
                    'image_path' => $path,
                ]);
            }
        }

        // Save the updated request
        $hairStylistRequest->save();

        // Prepare the response data
        $responseData = [
            'request_id' => $hairStylistRequest->id,
            'status' => $hairStylistRequest->status,
            'area_selection' => RequestArea::where('request_id', $hairStylistRequest->id)->pluck('area_id'),
            'menu_selection' => RequestMenu::where('request_id', $hairStylistRequest->id)->pluck('menu_id'),
            'hair_concerns' => $hairStylistRequest->hair_concerns,
            'image_paths' => RequestImage::where('request_id', $hairStylistRequest->id)->pluck('image_path'),
            'message' => 'Hair stylist request has been successfully updated.'
        ];

        // Return the response with the updated request details
        return response()->json($responseData, 200);
    }

    /**
     * Delete a specific image from a hair stylist request.
     *
     * @throws \Throwable
     * @param int $request_id The ID of the hair stylist request.
     * @param int $image_id The ID of the image to delete.
     * @return JsonResponse
     */
    public function deleteRequestImage(int $request_id, int $image_id): JsonResponse
    {
        try {
            // Ensure the request exists
            $hairStylistRequest = StylistRequest::findOrFail($request_id);

            // Ensure the image is related to the request
            $requestImage = RequestImage::where('request_id', $hairStylistRequest->id)
                                        ->where('id', $image_id)
                                        ->firstOrFail();

            // Use transaction to ensure data integrity
            DB::transaction(function () use ($requestImage) {
                // Delete the image
                $requestImage->delete();
            });

            // Return a success response
            return response()->json(['message' => 'Image has been successfully deleted.'], 200);
        } catch (ModelNotFoundException $e) {
            // Return an error response if the request or image doesn't exist
            return response()->json(['message' => 'Request or image not found.'], 404);
        } catch (\Exception $e) {
            // Return a generic error response for any other exceptions
            return response()->json(['message' => 'An error occurred while deleting the image.'], 500);
        }
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

        return Response::json([
            'request_id' => $stylistRequest->id,
            'request_time' => $requestTime->toDateTimeString(),
        ]);
    }

    // ... other methods ...
}
