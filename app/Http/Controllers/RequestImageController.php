
<?php

namespace App\Http\Controllers;

use App\Http\Requests\DeleteRequestImageRequest;
use App\Models\Request;
use App\Services\RequestImageService;
use Illuminate\Http\JsonResponse;

class RequestImageController extends Controller
{
    protected $requestImageService;

    public function __construct(RequestImageService $requestImageService)
    {
        $this->requestImageService = $requestImageService;
    }

    public function deleteRequestImage(DeleteRequestImageRequest $request): JsonResponse
    {
        $requestId = $request->validated()['request_id'];
        $imagePath = $request->validated()['image_path'];

        $requestModel = Request::findOrFail($requestId);
        $result = $this->requestImageService->deleteImage($requestId, $imagePath);

        if ($result) {
            $requestModel->touch(); // Update the "updated_at" timestamp
            return response()->json(['message' => 'Image deleted successfully.'], 200);
        }

        return response()->json(['message' => 'Failed to delete image.'], 500);
    }
}
