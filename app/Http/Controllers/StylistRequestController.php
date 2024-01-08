<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateStylistRequest;
use App\Services\StylistRequestService;
use App\Http\Requests\CreateHairStylistRequest;
use App\Http\Requests\UpdateHairStylistRequest;
use App\Http\Requests\CancelStylistRequest; // Added import for CancelStylistRequest
use App\Http\Resources\StylistRequestResource; // Existing import for StylistRequestResource
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
            $stylistRequestResource = new StylistRequestResource($hairStylistRequest); // Use StylistRequestResource for response

            return response()->json($stylistRequestResource, 201); // Return the resource instead of raw data
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

    public function cancelStylistRequest(CancelStylistRequest $request): JsonResponse
    {
        try {
            $validatedData = $request->validated();
            $confirmationMessage = $this->stylistRequestService->cancelRequest($validatedData['id'], $validatedData['user_id']);

            return response()->json([
                'message' => $confirmationMessage
            ], 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function updateStylistRequest(UpdateHairStylistRequest $request): JsonResponse // Use UpdateHairStylistRequest for consistency
    {
        try {
            $validatedData = $request->validated();
            $stylistRequest = $this->stylistRequestService->updateStylistRequest($validatedData); // Use the correct service method

            return response()->json([
                'stylist_request' => $stylistRequest,
                'message' => 'Stylist request updated successfully.'
            ], 200);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    // ... other methods ...

    // Add any additional methods you need here

}

// End of StylistRequestController class
