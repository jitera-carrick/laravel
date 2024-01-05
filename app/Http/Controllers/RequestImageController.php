
<?php

use App\Http\Requests\DeleteImageRequest;
use App\Models\Request as HairStylistRequest;
use App\Models\RequestImage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

use App\Services\RequestImageService;

class RequestImageController extends Controller
{
    // ... other methods ...

    /**
     * Delete a specific image from a hair stylist request.
     * @param int $request_id The ID of the hair stylist request.
     * @param int $image_id The ID of the image to delete.
     * @return JsonResponse
     */
    public function deleteRequestImage(int $request_id, int $image_id): JsonResponse
    {
        try {
            // Validate the incoming request using DeleteImageRequest
            $validatedData = (new DeleteImageRequest())->validateResolved();

            // Ensure the request exists and the user is authorized to delete the image
            $hairStylistRequest = HairStylistRequest::where('id', $validatedData['request_id'])
                                                     ->whereHas('user', function ($query) {
                                                         $query->where('id', Auth::id());
                                                     })
                                                     ->firstOrFail();

            // Find the image associated with the request and image ID
            $requestImage = RequestImage::where('request_id', $hairStylistRequest->id)
                                        ->where('id', $validatedData['image_id'])
                                        ->firstOrFail();

            // Optionally, remove the image file from storage
            if (Storage::disk('public')->exists($requestImage->image_path)) {
                Storage::disk('public')->delete($requestImage->image_path);
            }

            // Use the RequestImageService to delete the image record
            $requestImageService = new RequestImageService();
            $requestImageService->deleteRequestImage($requestImage);

            // Return a success response
            return response()->json(['message' => 'Image has been successfully deleted.']);
        } catch (ModelNotFoundException $e) {
            // Return an error response if the request or image doesn't exist
            return response()->json(['error' => 'The requested resource was not found.'], 404);
        } catch (\Exception $e) {
            // Return a generic error response for any other exceptions
            return response()->json(['error' => 'An error occurred while deleting the image.'], 500);
        }
    }

    // ... other methods ...
}
