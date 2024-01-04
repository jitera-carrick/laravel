<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request as HttpRequest;
use App\Http\Requests\CreateHairStylistRequest;
use App\Http\Requests\UpdateHairStylistRequest;
use App\Models\Request;
use App\Models\RequestAreaSelection;
use App\Models\RequestMenuSelection;
use App\Models\RequestImage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class RequestController extends Controller
{
    // ... other methods ...
    
    // Method to create a hair stylist request
    public function createHairStylistRequest(HttpRequest $httpRequest): JsonResponse
    {
        // ... existing code for createHairStylistRequest method ...
    }

    // Method to update a hair stylist request
    public function updateHairStylistRequest(UpdateHairStylistRequest $request, $id): JsonResponse
    {
        $hairStylistRequest = Request::findOrFail($id);

        // Authorization check
        if ($hairStylistRequest->user_id !== Auth::id() && !Auth::user()->isAdmin()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        // Validate the request
        $validator = Validator::make($request->all(), [
            'area' => 'sometimes|exists:request_areas,id',
            'menu' => 'sometimes|exists:request_menus,id',
            'status' => 'sometimes|in:pending,confirmed,cancelled', // Assuming these are the valid statuses
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        // Update the request with validated data
        $hairStylistRequest->fill($request->validated());
        $hairStylistRequest->save();

        // Return the updated request
        return response()->json([
            'status' => 200,
            'request' => $hairStylistRequest
        ]);
    }

    // Method to update a hair stylist request (old method, keep for backward compatibility)
    public function update(UpdateHairStylistRequest $request, $id): JsonResponse
    {
        // ... existing code for update method ...
    }

    // Method to delete an image from a hair stylist request
    public function deleteImage(HttpRequest $httpRequest, $request_id, $image_id): JsonResponse
    {
        // ... existing code for deleteImage method ...
    }

    // ... other methods ...
}
