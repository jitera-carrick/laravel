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

    // Method to delete an image from a hair stylist request
    public function deleteImage($request_id, $image_id): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $request = Request::find($request_id);
        if (!$request) {
            return response()->json(['message' => 'Invalid request ID.'], 404);
        }

        $image = RequestImage::where('id', $image_id)->where('request_id', $request->id)->first();
        if (!$image) {
            return response()->json(['message' => 'Invalid image ID or image does not belong to the specified request.'], 404);
        }

        if ($user->id !== $request->user_id && !$user->isAdmin()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        try {
            $image->delete();
            return response()->json(['status' => 200, 'message' => 'Image has been successfully deleted.']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An unexpected error occurred on the server.'], 500);
        }
    }

    // ... other methods ...
}
