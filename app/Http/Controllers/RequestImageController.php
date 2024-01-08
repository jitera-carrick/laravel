<?php

namespace App\Http\Controllers;

use App\Http\Requests\DeleteRequestImageRequest;
use App\Models\RequestImage;
use App\Models\HairStylistRequest;
use App\Services\RequestImageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class RequestImageController extends Controller
{
    /**
     * Delete a request image.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function deleteRequestImage(int $id): JsonResponse
    {
        // The user needs to be authenticated.
        if (!Auth::check()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // The user must have permission to delete the request image.
        // Assuming there is a method `canDeleteRequestImage` to check permission
        if (!Auth::user()->canDeleteRequestImage()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        try {
            // Validate the input "id" and find the request image record
            if (!is_int($id)) {
                return response()->json(['message' => 'Invalid format for image ID.'], 422);
            }

            $requestImage = RequestImage::findOrFail($id);

            // Check for linked hair stylist requests and update them if necessary
            $linkedHairStylistRequests = HairStylistRequest::where('request_image_id', $requestImage->id)->get();
            foreach ($linkedHairStylistRequests as $hairStylistRequest) {
                $hairStylistRequest->request_image_id = null;
                $hairStylistRequest->save();
            }

            // Call the service to handle the deletion of the image file and record
            $requestImageService = new RequestImageService();
            $requestImageService->deleteImage($requestImage->id);

            // Return a confirmation message upon successful deletion
            return response()->json(['status' => 200, 'message' => 'Request image deleted successfully.'], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Image not found.'], 400);
        } catch (\Exception $e) {
            Log::error('Failed to delete request image: ' . $e->getMessage());

            // Return an error message if the process fails
            return response()->json(['message' => 'Error occurred while deleting the request image.'], 500);
        }
    }
}
