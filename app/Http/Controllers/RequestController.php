<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateHairStylistRequest;
use App\Http\Requests\UpdateStylistRequestStatusRequest; // Import the new request validation class
use App\Models\Request;
use App\Models\StylistRequest; // Import the StylistRequest model
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class RequestController extends Controller
{
    // ... other methods ...

    // Method to update a hair stylist request
    public function update(UpdateHairStylistRequest $request, $id): JsonResponse
    {
        // ... existing update method code ...
    }

    // Method to update the status of a stylist request
    public function updateStylistRequestStatus(UpdateStylistRequestStatusRequest $request, $id): JsonResponse
    {
        $stylistRequest = StylistRequest::find($id);

        // Check if the stylist request exists
        if (!$stylistRequest) {
            return response()->json(['message' => 'The stylist request is not found.'], 404);
        }

        // Validate the request parameters
        $validated = $request->validated();

        DB::beginTransaction();
        try {
            // Update the status
            $stylistRequest->status = $validated['status'];
            $stylistRequest->save();

            DB::commit();

            return response()->json([
                'status' => 200,
                'request_id' => $stylistRequest->id,
                'updated_status' => $stylistRequest->status,
                'message' => 'The status of the request has been successfully updated.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to update the request status.'], 500);
        }
    }

    // ... other methods ...
}
