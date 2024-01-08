<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateStylistRequest;
use App\Services\StylistRequestService;
use App\Http\Requests\CreateHairStylistRequest;
use App\Http\Requests\UpdateHairStylistRequest;
use Illuminate\Http\JsonResponse;
use Exception;

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

    public function createHairStylistRequest(CreateHairStylistRequest $request): JsonResponse
    {
        try {
            $validatedData = $request->validated();
            $hairStylistRequest = $this->stylistRequestService->createRequest($validatedData);

            return response()->json($hairStylistRequest, 201);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function updateHairStylistRequest(UpdateHairStylistRequest $request, $request_id): JsonResponse
    {
        try {
            $validatedData = $request->validated();
            $validatedData['request_id'] = $request_id;
            $hairStylistRequest = $this->stylistRequestService->updateRequest($validatedData);

            return response()->json($hairStylistRequest, 200);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
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
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function createOrUpdateHairStylistRequest(CreateHairStylistRequest $request): JsonResponse
    {
        try {
            $validatedData = $request->validated();
            if (isset($validatedData['request_id'])) {
                $hairStylistRequest = $this->stylistRequestService->updateRequest($validatedData);
            } else {
                $hairStylistRequest = $this->stylistRequestService->createRequest($validatedData);
            }
            return response()->json($hairStylistRequest, isset($validatedData['request_id']) ? 200 : 201);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    // ... other methods ...

    // Add any additional methods you need here

}

// End of StylistRequestController class
