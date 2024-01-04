<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request as HttpRequest;
use App\Http\Requests\UpdateHairStylistRequest;
use App\Http\Requests\CreateHairStylistRequest;
use App\Models\Request;
use App\Models\RequestAreaSelection;
use App\Models\RequestMenuSelection;
use App\Models\RequestImage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class RequestController extends Controller
{
    // ... other methods ...
    
    // Method to create a hair stylist request
    public function createHairStylistRequest(HttpRequest $httpRequest): JsonResponse
    {
        $user = Auth::user();

        // Validate the request
        $validator = Validator::make($httpRequest->all(), [
            'user_id' => 'required|exists:users,id',
            'area_id' => 'required|array|min:1',
            'menu_id' => 'required|array|min:1',
            'hair_concerns' => 'required|string|max:3000',
            'image_path' => 'required|array|max:3',
            'image_path.*' => 'file|image|mimes:png,jpg,jpeg|max:5120', // 5MB
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        if ($httpRequest->user_id != $user->id) {
            return response()->json(['message' => 'Unauthorized user.'], 401);
        }

        // Create the request
        $hairRequest = Request::create([
            'user_id' => $httpRequest->user_id,
            'hair_concerns' => $httpRequest->hair_concerns,
            'status' => 'pending', // Assuming 'pending' is a valid status
        ]);

        // Create area selections
        foreach ($httpRequest->area_id as $areaId) {
            RequestAreaSelection::create([
                'request_id' => $hairRequest->id,
                'area_id' => $areaId,
            ]);
        }

        // Create menu selections
        foreach ($httpRequest->menu_id as $menuId) {
            RequestMenuSelection::create([
                'request_id' => $hairRequest->id,
                'menu_id' => $menuId,
            ]);
        }

        // Create images
        foreach ($httpRequest->image_path as $image) {
            $imagePath = Storage::disk('public')->put('request_images', $image);
            RequestImage::create([
                'request_id' => $hairRequest->id,
                'image_path' => $imagePath,
            ]);
        }

        return response()->json([
            'status' => 201,
            'request' => [
                'id' => $hairRequest->id,
                'area_id' => $httpRequest->area_id,
                'menu_id' => $httpRequest->menu_id,
                'hair_concerns' => $httpRequest->hair_concerns,
                'status' => 'pending',
                'created_at' => $hairRequest->created_at->toIso8601String(),
                'user_id' => $user->id,
            ],
        ], 201);
    }

    // ... other methods ...

    // Method to update a hair stylist request
    public function updateHairStylistRequest(UpdateHairStylistRequest $request, Request $hairRequest): JsonResponse
    {
        $user = Auth::user();

        if (!$hairRequest || $hairRequest->user_id !== $user->id) {
            return response()->json(['message' => 'Request not found or unauthorized.'], 404);
        }

        $validated = $request->validated();

        // Update area and menu selections within a transaction
        DB::transaction(function () use ($request, $hairRequest) {
            // Update area selections
            RequestAreaSelection::where('request_id', $hairRequest->id)->delete();
            foreach ($request->area as $areaId) {
                RequestAreaSelection::create([
                    'request_id' => $hairRequest->id,
                    'area_id' => $areaId,
                ]);
            }

            // Update menu selections
            RequestMenuSelection::where('request_id', $hairRequest->id)->delete();
            foreach ($request->menu as $menuId) {
                RequestMenuSelection::create([
                    'request_id' => $hairRequest->id,
                    'menu_id' => $menuId,
                ]);
            }

            // Update hair concerns
            $hairRequest->update(['hair_concerns' => $request->hair_concerns]);

            // Update images
            RequestImage::where('request_id', $hairRequest->id)->delete();
            foreach ($request->images as $image) {
                // Store the image and get the path
                $imagePath = Storage::disk('public')->put('request_images', $image);
                RequestImage::create([
                    'request_id' => $hairRequest->id,
                    'image_path' => $imagePath,
                ]);
            } 
        });

        return response()->json([
            'status' => 200,
            'message' => 'Hair stylist request updated successfully',
            'request_id' => $hairRequest->id,
            'request' => $hairRequest->fresh(),
        ]); 
    }

    // Method to delete an image from a hair stylist request
    public function deleteImage(HttpRequest $httpRequest, $request_id, $image_id): JsonResponse
    {
        $user = Auth::user();
        $request = Request::where('id', $request_id)->where('user_id', $user->id)->first();

        if (!$request) {
            return response()->json(['message' => 'Request not found or unauthorized.'], 404);
        }

        $requestImage = RequestImage::where('id', $image_id)->where('request_id', $request_id)->first();

        if (!$requestImage) {
            return response()->json(['message' => 'Image not found or does not belong to the request.'], 404);
        }

        try {
            $requestImage->delete();
            return response()->json(['message' => 'Image deleted successfully.'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to delete the image.'], 500);
        }
    }

    // ... other methods ...
}
