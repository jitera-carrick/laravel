
<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateHairStylistRequest;
use App\Services\HairStylistRequestService;
use App\Http\Resources\HairStylistRequestResource;
use Illuminate\Http\JsonResponse;

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

    public function cancelHairStylistRequest(CancelHairStylistRequest $request): JsonResponse
    {
        $validatedData = $request->validated();
        $id = $validatedData['id'];
        $result = $this->hairStylistRequestService->cancelRequest($id);

        return response()->json([
            'message' => 'Hair stylist request cancelled successfully.',
            'data' => $result
        ]);
    }
    // ... other methods ...
}
