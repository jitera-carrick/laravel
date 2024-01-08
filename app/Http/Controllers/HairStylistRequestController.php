
<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateHairStylistRequest;
use App\Http\Requests\DeleteHairStylistRequestImageRequest;
use App\Services\HairStylistRequestService;
use App\Http\Resources\HairStylistRequestResource;
use Illuminate\Http\JsonResponse;
use App\Exceptions\CustomException;

class HairStylistRequestController extends Controller
{
    protected $hairStylistRequestService;

    public function __construct(HairStylistRequestService $hairStylistRequestService)
    {
        $this->hairStylistRequestService = $hairStylistRequestService;
    }

    public function createHairStylistRequest(CreateHairStylistRequest $request): JsonResponse
    {
        $validatedData = $request->validated();
        $hairStylistRequest = $this->hairStylistRequestService->createRequest($validatedData);

        return response()->json(new HairStylistRequestResource($hairStylistRequest), 201);
    }

    public function deleteHairStylistRequestImages(DeleteHairStylistRequestImageRequest $request): JsonResponse
    {
        $validatedData = $request->validated();
        $hairStylistRequestId = $validatedData['id'];

        try {
            $this->hairStylistRequestService->deleteImagesByHairStylistRequestId($hairStylistRequestId);
            return response()->json(['message' => 'Images deleted successfully.'], 200);
        } catch (CustomException $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred while deleting images.'], 500);
        }
    }

    // ... other methods ...
}
