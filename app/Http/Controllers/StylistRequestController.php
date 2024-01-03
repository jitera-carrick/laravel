<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateStylistRequest;
use App\Http\Resources\StylistRequestResource;
use App\Services\StylistRequestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class StylistRequestController extends Controller
{
    protected $stylistRequestService;

    public function __construct(StylistRequestService $stylistRequestService)
    {
        $this->stylistRequestService = $stylistRequestService;
    }

    // ... other methods ...

    // Method to create a new stylist request
    public function createStylistRequest(CreateStylistRequest $request): JsonResponse
    {
        $validated = $request->validated();

        // Check if details are not empty
        if (empty($validated['details'])) {
            return response()->json(['error' => 'Details field is required.'], 422);
        }

        // Check if user exists
        $userExists = DB::table('users')->where('id', $validated['user_id'])->exists();
        if (!$userExists) {
            return response()->json(['error' => 'User not found.'], 404);
        }

        // Create the stylist request with status 'pending'
        $stylistRequest = $this->stylistRequestService->createRequest($validated['user_id'], $validated['details'], 'pending');

        // Return the response with the id and status of the new stylist request
        return response()->json([
            'id' => $stylistRequest->id,
            'status' => $stylistRequest->status
        ], 201);
    }

    // ... other methods ...
}
