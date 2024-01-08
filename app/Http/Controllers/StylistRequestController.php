<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateStylistRequest;
use App\Services\StylistRequestService;
use App\Http\Requests\CreateHairStylistRequest;
use App\Http\Requests\UpdateHairStylistRequest;
use App\Http\Requests\CancelStylistRequest;
use App\Http\Resources\StylistRequestResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
            $stylistRequestResource = new StylistRequestResource($hairStylistRequest);

            return response()->json($stylistRequestResource, 201);
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

    public function cancelStylistRequest($request, $id): JsonResponse
    {
        try {
            if ($request instanceof Request) {
                $request->validate([
                    'id' => 'required|integer|exists:stylist_requests,id'
                ]);
                $userId = auth()->id(); // Assuming the user is authenticated and you can get their ID
            } elseif ($request instanceof CancelStylistRequest) {
                $validatedData = $request->validated();
                $id = $validatedData['id'];
                $userId = $validatedData['user_id'];
            } else {
                throw new Exception("Invalid request type");
            }

            $stylistRequest = $this->stylistRequestService->cancelRequest($id, $userId);

            return response()->json([
                'status' => 200,
                'message' => 'Stylist request canceled successfully.',
                'stylist_request' => $stylistRequest
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['error' => $e->errors()], 422);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function updateStylistRequest(UpdateStylistRequest $request, $id): JsonResponse
    {
        try {
            $validatedData = $request->validated();
            $validatedData['id'] = $id;
            $stylistRequest = $this->stylistRequestService->updateStylistRequest($validatedData);
            $stylistRequestResource = new StylistRequestResource($stylistRequest);

            return response()->json($stylistRequestResource, 200);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    // ... other methods ...

    // Add any additional methods you need here

}

// End of StylistRequestController class
