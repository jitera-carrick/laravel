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
        $validator = Validator::make($httpRequest->all(), [
            'area' => 'required|exists:request_areas,id',
            'menu' => 'required|exists:request_menus,id',
            'hair_concerns' => 'required|string',
            'status' => 'required|in:pending,completed,cancelled', // Assuming these are the valid statuses
            'user_id' => 'required|exists:users,id',
        ], [
            'area.required' => 'Area is required and must be valid.',
            'menu.required' => 'Menu is required and must be valid.',
            'hair_concerns.required' => 'Hair concerns are required.',
            'status.in' => 'Invalid status.',
            'user_id.exists' => 'Invalid user ID.',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $user = Auth::user();
        if ($httpRequest->user_id != $user->id) {
            return response()->json(['message' => 'Unauthorized user.'], 401);
        }

        // Create the request
        $hairRequest = Request::create([
            'user_id' => $user->id,
            'area_id' => $httpRequest->area,
            'menu_id' => $httpRequest->menu,
            'hair_concerns' => $httpRequest->hair_concerns,
            'status' => $httpRequest->status,
        ]);

        // Assuming RequestAreaSelection and RequestMenuSelection are the correct models for area and menu selections
        RequestAreaSelection::create([
            'request_id' => $hairRequest->id,
            'area_id' => $httpRequest->area,
        ]);

        RequestMenuSelection::create([
            'request_id' => $hairRequest->id,
            'menu_id' => $httpRequest->menu,
        ]);

        // Assuming RequestImage is the correct model for images
        if ($httpRequest->hasFile('image_path')) {
            foreach ($httpRequest->image_path as $image) {
                $imagePath = Storage::disk('public')->put('request_images', $image);
                RequestImage::create([
                    'request_id' => $hairRequest->id,
                    'image_path' => $imagePath,
                ]);
            }
        }

        return response()->json([
            'status' => 201,
            'request' => [
                'id' => $hairRequest->id,
                'area' => $hairRequest->area_id,
                'menu' => $hairRequest->menu_id,
                'hair_concerns' => $hairRequest->hair_concerns,
                'status' => $hairRequest->status,
                'created_at' => $hairRequest->created_at->toIso8601String(),
                'user_id' => $hairRequest->user_id,
            ]
        ], 201);
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
