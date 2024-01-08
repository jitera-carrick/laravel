<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Requests\CreateHairStylistRequest;
use App\Http\Requests\UpdateHairStylistRequest;
use App\Http\Requests\DeleteImageRequest;
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
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Http\Resources\SuccessResource;

class UserController extends Controller
{
    // ... other methods ...

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
    public function updateHairStylistRequest(UpdateHairStylistRequest $request, int $id): JsonResponse
    {
        $hairStylistRequest = HairStylistRequest::find($id);

        if (!$hairStylistRequest) {
            return response()->json(['message' => 'Hair stylist request not found.'], 404);
        }

        if ($hairStylistRequest->user_id != Auth::id()) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        $validatedData = $request->validated();

        if ($request->has('details')) {
            $hairStylistRequest->details = $validatedData['details'];
        }

        if ($request->has('status')) {
            $allowedStatuses = ['pending', 'approved', 'rejected']; // Define your statuses
            $status = $validatedData['status'];
            if (in_array($status, $allowedStatuses)) {
                $hairStylistRequest->status = $status;
            } else {
                return response()->json(['message' => 'Invalid status value.'], 422);
            }
        }

        $hairStylistRequest->save();

        return response()->json(['message' => 'Hair stylist request updated successfully.'], 200);
    }

    /**
     * Delete a specific image from a hair stylist request.
     *
     * @param DeleteImageRequest $request
     * @return JsonResponse
     */
    public function deleteRequestImage(DeleteImageRequest $request): JsonResponse
    {
        $requestService = new RequestService();
        $imageService = new ImageService(); // Use ImageService for deleting the image file
        try {
            // Ensure the request exists
            $hairStylistRequest = HairStylistRequest::findOrFail($request->request_id);

            // Find the image associated with the request and image ID
            $requestImage = RequestImage::where('request_id', $hairStylistRequest->id)
                                        ->where('id', $request->image_id)->firstOrFail();

            // Delete the image file from storage
            $imageService->delete($requestImage->image_path);

            // Delete the image record
            $requestService->deleteRequestImage($request->request_id, $request->image_id);

            // Return a success response
            return new SuccessResource(['message' => 'Image has been successfully deleted.']);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Request or image not found.'], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred while deleting the image.'], 500);
        }
    }

    // ... other methods ...
}
