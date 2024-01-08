
<?php

namespace App\Http\Controllers;

use App\Http\Requests\DeleteRequestImageRequest;
use App\Services\RequestService;
use Illuminate\Http\JsonResponse;

class RequestImageController extends Controller
{
    public function deleteRequestImage(DeleteRequestImageRequest $request, $request_image_id): JsonResponse
    {
        $requestService = new RequestService();
        try {
            $confirmationMessage = $requestService->deleteRequestImage($request_image_id);
            return response()->json(['message' => $confirmationMessage]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
