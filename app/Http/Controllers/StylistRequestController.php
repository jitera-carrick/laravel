<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateStylistRequest;
use App\Models\User;
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

        // Check if user_id is an integer
        if (!is_int($validated['user_id'])) {
            return response()->json(['error' => 'User ID must be a number.'], 400);
        }

        // Check if details are not empty
        if (empty($validated['details'])) {
            return response()->json(['error' => 'Details are required.'], 400);
        }

        // Check if user exists using the User model
        $user = User::find($validated['user_id']);
        if (!$user) {
            return response()->json(['error' => 'User not found.'], 404);
        }

        try {
            // Create the stylist request with status 'pending'
            $stylistRequest = $this->stylistRequestService->createRequest($validated['user_id'], $validated['details'], 'pending');

            // Return the response with the id and status of the new stylist request
            return response()->json([
                'status' => 200,
                'stylist_request' => [
                    'id' => $stylistRequest->id,
                    'user_id' => $stylistRequest->user_id,
                    'details' => $stylistRequest->details,
                    'status' => $stylistRequest->status
                ]
            ], 200);
        } catch (\Exception $e) {
            // Handle any exceptions that occur during the creation process
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // ... other methods ...
}
