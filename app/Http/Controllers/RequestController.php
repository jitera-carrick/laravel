<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request as HttpRequest;
use App\Http\Requests\UpdateHairStylistRequest;
use App\Models\User;
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
            'hair_concerns' => 'required|string',
            'image_path' => 'required|array|max:3',
            'image_path.*' => 'file|image|mimes:png,jpg,jpeg|max:5120',
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
            'hair_concerns' => $validator->validated()['hair_concerns'],
            'status' => 'pending',
        ]);

        // Create area selections
        foreach ($validator->validated()['area_id'] as $areaId) {
            RequestAreaSelection::create([
                'request_id' => $hairRequest->id,
                'area_id' => $areaId,
            ]);
        }

        // Create menu selections
        foreach ($validator->validated()['menu_id'] as $menuId) {
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

    // Method to update a hair stylist request
    public function updateHairStylistRequest(HttpRequest $httpRequest, $id): JsonResponse
    {
        $hairRequest = Request::find($id);
        $user = Auth::user();

        if (!$hairRequest || $hairRequest->user_id !== $user->id) {
            return response()->json(['message' => 'Request not found or unauthorized.'], 404);
        }

        $validator = Validator::make($httpRequest->all(), [
            'area' => 'sometimes|string',
            'menu' => 'sometimes|string',
            'hair_concerns' => 'sometimes|string|max:3000',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $validated = $validator->validated();

        DB::transaction(function () use ($validated, $hairRequest) {
            if (isset($validated['area'])) {
                // Assuming 'area' is a string and maps to a single area_id
                $areaId = RequestAreaSelection::where('name', $validated['area'])->value('id');
                RequestAreaSelection::where('request_id', $hairRequest->id)->update(['area_id' => $areaId]);
            }

            if (isset($validated['menu'])) {
                // Assuming 'menu' is a string and maps to a single menu_id
                $menuId = RequestMenuSelection::where('name', $validated['menu'])->value('id');
                RequestMenuSelection::where('request_id', $hairRequest->id)->update(['menu_id' => $menuId]);
            }

            if (isset($validated['hair_concerns'])) {
                $hairRequest->update(['hair_concerns' => $validated['hair_concerns']]);
            }
        });

        return response()->json([
            'status' => 200,
            'request' => $hairRequest->fresh(),
        ]);
    }

    // Method to delete an image from a hair stylist request
    public function deleteImage(HttpRequest $httpRequest, $request_id, $image_id): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        $stylistRequest = Request::where('id', $request_id)->where('user_id', $user->id)->first();

        if (!$stylistRequest) {
            return response()->json(['message' => 'Request not found.'], 404);
        }

        $requestImage = RequestImage::where('id', $image_id)->where('request_id', $request_id)->first();

        if (!$requestImage) {
            return response()->json(['message' => 'Image not found or does not belong to the specified request.'], 404);
        }

        try {
            // Delete the image file from storage
            if (Storage::disk('public')->exists($requestImage->image_path)) {
                Storage::disk('public')->delete($requestImage->image_path);
            }
            // Delete the image record from the database
            $requestImage->delete();
            return response()->json(['status' => 204], 204);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to delete the image.'], 500);
        }
    }

    // ... other methods ...
}
