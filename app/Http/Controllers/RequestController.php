<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateHairStylistRequest;
use App\Models\Request;
use App\Models\RequestAreaSelection;
use App\Models\RequestMenuSelection;
use App\Models\RequestImage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request as HttpRequest;

class RequestController extends Controller
{
    // ... other methods ...

    // Method to create a new request
    public function create(HttpRequest $httpRequest): JsonResponse
    {
        // ... existing code for create method ...
    }

    // Method to update a hair stylist request
    public function update(UpdateHairStylistRequest $httpRequest, $id): JsonResponse
    {
        $hairRequest = Request::find($id);
        $user = Auth::user();

        if (!$hairRequest || $hairRequest->user_id !== $user->id) {
            return response()->json(['message' => 'Request not found or unauthorized.'], 404);
        }

        // Validate the request
        $validator = Validator::make($httpRequest->all(), [
            'area' => 'required|array|min:1',
            'menu' => 'required|array|min:1',
            'hair_concerns' => 'required|string|max:3000',
            'images' => 'required|array|min:1',
            'images.*' => 'image|mimes:png,jpg,jpeg|max:5120', // 5MB
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        // Update area selections
        RequestAreaSelection::where('request_id', $id)->delete();
        foreach ($httpRequest->area as $areaId) {
            RequestAreaSelection::create([
                'request_id' => $id,
                'area_id' => $areaId,
            ]);
        }

        // Update menu selections
        RequestMenuSelection::where('request_id', $id)->delete();
        foreach ($httpRequest->menu as $menuId) {
            RequestMenuSelection::create([
                'request_id' => $id,
                'menu_id' => $menuId,
            ]);
        }

        // Update hair concerns
        $hairRequest->update(['hair_concerns' => $httpRequest->hair_concerns]);

        // Update images
        RequestImage::where('request_id', $id)->delete();
        foreach ($httpRequest->images as $image) {
            // Store the image and get the path
            $imagePath = Storage::disk('public')->put('request_images', $image);
            RequestImage::create([
                'request_id' => $id,
                'image_path' => $imagePath,
            ]);
        }

        return response()->json([
            'status' => 200,
            'request' => $hairRequest->fresh(),
        ]);
    }

    // Method to update the status of a request
    public function updateStatus(HttpRequest $httpRequest, $id): JsonResponse
    {
        $validator = Validator::make($httpRequest->all(), [
            'status' => 'required|string|in:pending,accepted,rejected,sent_to_hairstylist', // Add all valid status strings
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $request = Request::find($id);

        if (!$request) {
            return response()->json(['message' => 'Request not found.'], 404);
        }

        // Check if the status value is valid
        $validStatuses = ['pending', 'accepted', 'rejected', 'sent_to_hairstylist']; // Define all valid statuses
        if (!in_array($httpRequest->status, $validStatuses)) {
            return response()->json(['message' => 'Invalid status value.'], 400);
        }

        // Update the status of the request
        $request->status = $httpRequest->status;
        $request->save();

        return response()->json([
            'status' => 200,
            'request' => [
                'id' => $request->id,
                'status' => $request->status,
                'updated_at' => $request->updated_at->toIso8601String(),
            ],
        ]);
    }

    // ... other methods ...
}
