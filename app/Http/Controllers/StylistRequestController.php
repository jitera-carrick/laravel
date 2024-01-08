
<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateStylistRequest;
use App\Services\StylistRequestService;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\UpdateHairStylistRequest;

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

    public function cancelHairStylistRequest(UpdateHairStylistRequest $request): JsonResponse
    {
        try {
            $userId = $request->input('user_id');
            $requestId = $request->input('request_id');
            $result = $this->stylistRequestService->cancelRequest($userId, $requestId);

            return response()->json([
                'message' => 'Hair stylist request canceled successfully.'
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // ... other methods ...
}
