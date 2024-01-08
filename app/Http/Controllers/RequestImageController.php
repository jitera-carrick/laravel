
<?php

namespace App\Http\Controllers;

use App\Http\Requests\DeleteRequestImageRequest;
use App\Services\RequestService;
use App\Services\RequestImageService;
use Illuminate\Http\JsonResponse;
use App\Models\RequestImage;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class RequestImageController extends Controller
{
    public function deleteRequestImage(DeleteRequestImageRequest $request): JsonResponse
    {
        // Ensure the user is authenticated
        if (!auth()->check()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $requestImageService = new RequestImageService();
        try {
            // Check if the authenticated user is authorized to delete the image
            $requestImage = RequestImage::findOrFail($request->request_image_id);
            $userId = auth()->user()->id;
            $userOwnsImage = $requestImage->hairStylistRequest->user_id === $userId;

            if (!$userOwnsImage) {
                return response()->json(['message' => 'Forbidden'], 403);
            }

            // Use the RequestImageService to delete the image
            $confirmationMessage = $requestImageService->deleteRequestImage($request->request_image_id);
            return response()->json(['message' => $confirmationMessage], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Image not found.'], 404);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    // ... other methods ...
}
