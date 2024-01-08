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

    // ... other methods ...

    public function cancelStylistRequest(Request $request, $id): JsonResponse
    {
        try {
            $request->validate([
                'id' => 'required|integer|exists:stylist_requests,id'
            ]);

            $userId = auth()->id(); // Assuming the user is authenticated and you can get their ID
            $stylistRequest = $this->stylistRequestService->cancelRequest($id, $userId);

            return response()->json([
                'status' => 200,
                'stylist_request' => $stylistRequest
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['error' => $e->errors()], 422);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // ... other methods ...
}

// End of StylistRequestController class
