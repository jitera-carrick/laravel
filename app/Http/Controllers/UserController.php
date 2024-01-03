<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateHairStylistRequest;
use App\Http\Requests\UpdateHairStylistRequest;
use App\Models\User;
use App\Models\Request as HairStylistRequest;
use App\Models\RequestArea;
use App\Models\RequestMenu;
use App\Models\RequestImage;
use App\Models\StylistRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    // ... other methods ...

    // Existing methods remain unchanged

    // Method to create or update a hair stylist request
    // ... existing code for createOrUpdateHairStylistRequest ...

    // Method to cancel a hair stylist request
    // ... existing code for cancelHairStylistRequest ...

    // Method to update a hair stylist request
    // ... existing code for updateHairStylistRequest ...

    /**
     * Update the status of a stylist request.
     *
     * @param HttpRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateStylistRequestStatus(HttpRequest $request): JsonResponse
    {
        // Start a database transaction
        DB::beginTransaction();
        try {
            // Validate the request input
            $validatedData = $request->validate([
                'id' => 'required|exists:stylist_requests,id',
                'status' => 'required|string'
            ]);

            // Find the stylist request by ID
            $stylistRequest = StylistRequest::findOrFail($validatedData['id']);

            // Allowed statuses
            $allowedStatuses = ['received', 'in progress', 'completed']; // Updated allowed statuses as per requirement

            // Validate the status value
            if (!in_array($validatedData['status'], $allowedStatuses)) {
                return response()->json(['message' => 'Invalid status value.'], 422);
            }

            // Update the status of the stylist request
            $stylistRequest->status = $validatedData['status'];
            $stylistRequest->save();

            // Commit the transaction
            DB::commit();

            // Prepare the response data
            $responseData = [
                'request_id' => $stylistRequest->id,
                'status' => $stylistRequest->status,
                'message' => 'The status of the stylist request has been successfully updated.'
            ];

            // Return a successful response with the updated request details
            return response()->json($responseData, 200);
        } catch (\Throwable $e) {
            // Rollback the transaction in case of any error
            DB::rollBack();

            // Return an error response
            return response()->json(['message' => 'Failed to update the request status.'], 500);
        }
    }

    // ... other methods ...
}
