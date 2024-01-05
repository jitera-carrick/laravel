
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

class RequestImageController extends Controller
{
    // ... other methods ...

    /**
     * Delete an image from a request.
     *
     * @param int $request_id
     * @param int $image_id
     * @return JsonResponse
     */
    public function deleteRequestImage(int $request_id, int $image_id): JsonResponse
    {
        try {
            // Validate the incoming request using DeleteImageRequest
            $validatedData = (new DeleteImageRequest())->validateResolved();

            // Ensure the request exists and the authenticated user has permission to delete the image
            $hairStylistRequest = HairStylistRequest::findOrFail($validatedData['request_id']);
            if ($hairStylistRequest->user_id != Auth::id()) {
                return response()->json(['message' => 'Unauthorized access.'], 403);
            }

            // Use the RequestImageService to delete the image record
            $requestImageService = new RequestImageService();
            $requestImageService->deleteImage($validatedData['image_id']);

            // Remove the image file from the storage
            Storage::disk('public')->delete($requestImage->image_path);

            // Return a success response
            return response()->json(['message' => 'Image has been successfully deleted.']);
        } catch (ModelNotFoundException $e) {
            // Return an error response if the request or image doesn't exist
            return response()->json(['message' => 'Request or image not found.'], 404);
        } catch (\Exception $e) {
            // Return a generic error response for any other exceptions
            return response()->json(['message' => 'An error occurred while deleting the image.'], 500);
        }
    }

    // ... other methods ...
}
