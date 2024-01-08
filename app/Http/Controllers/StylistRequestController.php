
<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateStylistRequest;
use App\Http\Requests\DeleteRequestImageRequest;
use App\Services\StylistRequestService;
use Illuminate\Http\JsonResponse;

class StylistRequestController extends Controller
{
    protected $stylistRequestService;

    public function __construct(StylistRequestService $stylistRequestService)
    {
        $this->stylistRequestService = $stylistRequestService;
    }

    public function createStylistRequest(CreateStylistRequest $request): JsonResponse
    {
        $validatedData = $request->validated();
        $stylistRequestId = $this->stylistRequestService->createRequest($validatedData);

        return response()->json([
            'stylist_request_id' => $stylistRequestId,
            'message' => 'Stylist request created successfully.'
        ], 201);
    }

    public function deleteRequestImage(DeleteRequestImageRequest $request): JsonResponse
    {
        try {
            $deletedImageId = $this->stylistRequestService->deleteImage($request->request_id, $request->image_id);

            return response()->json([
                'image_id' => $deletedImageId,
                'message' => 'Image deleted successfully.'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while deleting the image: ' . $e->getMessage()
            ], 500);
        }
    }

    // ... other methods ...
}
