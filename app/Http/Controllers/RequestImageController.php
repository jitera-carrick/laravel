
<?php

namespace App\Http\Controllers;

use App\Http\Requests\DeleteRequestImageRequest;
use App\Models\RequestImage;
use App\Models\HairStylistRequest;
use App\Services\RequestImageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class RequestImageController extends Controller
{
    /**
     * Delete a request image.
     *
     * @param DeleteRequestImageRequest $request
     * @return JsonResponse
     */
    public function deleteRequestImage(DeleteRequestImageRequest $request): JsonResponse
    {
        try {
            // Validate the input "id" and find the request image record
            $requestImage = RequestImage::findOrFail($request->validated()['id']);

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
            return response()->json(['message' => 'Request image deleted successfully.'], 200);
        } catch (\Exception $e) {
            Log::error('Failed to delete request image: ' . $e->getMessage());

            // Return an error message if the process fails
            return response()->json(['message' => 'Error occurred while deleting the request image.'], 500);
        }
    }
}
