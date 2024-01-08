
<?php

namespace App\Http\Controllers;

use App\Http\Requests\DeleteRequestImageRequest;
use App\Services\RequestImageService;
use App\Http\Resources\SuccessResource;
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
        $validated = $request->validated();
        $this->requestImageService->deleteImage($validated['request_id'], $validated['image_id']);

        return new SuccessResource(['message' => 'Image has been successfully deleted.']);
    }
}
