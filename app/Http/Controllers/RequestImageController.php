<?php

use App\Http\Requests\DeleteImageRequest;
use App\Models\Request as HairStylistRequest;
use App\Models\RequestImage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Services\RequestImageService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class RequestImageController extends Controller
{
    // ... other methods ...

    /**
     * Delete an image from a hair stylist request.
     *
     * @param int $request_id
     * @param int $image_id
     * @return JsonResponse
     */
    public function deleteHairStylistRequestImage(int $request_id, int $image_id): JsonResponse
    {
        try {
            // Validate that the request_id exists in the requests table
            $hairStylistRequest = HairStylistRequest::findOrFail($request_id);

            // Validate that the image_id exists in the request_images table and is associated with the request_id
            $requestImage = RequestImage::where('id', $image_id)
                                        ->where('request_id', $request_id)
                                        ->firstOrFail();

            // Ensure the authenticated user is the owner of the request or an admin
            if ($hairStylistRequest->user_id != Auth::id() && !Auth::user()->isAdmin()) {
                return response()->json(['message' => 'Unauthorized access.'], 403);
            }

            // Use the RequestImageService to delete the image record
            $requestImageService = new RequestImageService();
            $requestImageService->deleteImage($image_id);

            // Remove the image file from the storage
            Storage::disk('public')->delete($requestImage->image_path);

            // Return a success response
            return response()->json(['message' => 'Image has been successfully deleted.'], 200);
        } catch (ModelNotFoundException $e) {
            // Return an error response if the request or image doesn't exist
            $notFoundMessage = $e->getModel() == HairStylistRequest::class ? 'Request not found.' : 'Image not found or does not belong to the specified request.';
            return response()->json(['message' => $notFoundMessage], 404);
        } catch (\Exception $e) {
            // Return a generic error response for any other exceptions
            return response()->json(['message' => 'An error occurred while deleting the image.'], 500);
        }
    }

    // ... other methods ...
}
