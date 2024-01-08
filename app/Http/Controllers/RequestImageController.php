
<?php

namespace App\Http\Controllers;

use App\Http\Requests\DeleteImageRequest;
use App\Services\RequestImageService;
use Illuminate\Http\JsonResponse;

class RequestImageController extends Controller
{
    protected $requestImageService;

    public function __construct(RequestImageService $requestImageService)
    {
        $this->requestImageService = $requestImageService;
    }

    public function deleteRequestImage(DeleteImageRequest $request): JsonResponse
    {
        $validatedData = $request->validated();
        $isDeleted = $this->requestImageService->deleteImageById($validatedData['image_id']);

        if ($isDeleted) {
            return response()->json(['message' => 'Image deleted successfully.'], 200);
        }

        return response()->json(['message' => 'Failed to delete the image.'], 500);
    }
}
