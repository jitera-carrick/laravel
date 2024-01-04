<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request as HttpRequest;
use App\Http\Requests\UpdateHairStylistRequest;
use App\Http\Requests\CreateHairStylistRequest;
use App\Http\Requests\DeleteImageRequest;
use App\Models\Request;
use App\Models\RequestAreaSelection;
use App\Models\RequestMenuSelection;
use App\Models\RequestImage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\SuccessResource;
use App\Models\StylistRequest;

class RequestController extends Controller
{
    // ... other methods ...

    // Method to create a hair stylist request
    public function createHairStylistRequest(HttpRequest $httpRequest): JsonResponse
    {
        $user = Auth::user();

        // Determine if the request is an instance of CreateHairStylistRequest
        if ($httpRequest instanceof CreateHairStylistRequest) {
            if ($httpRequest->user_id != $user->id) {
                return response()->json(['message' => 'Unauthorized user.'], 401);
            }

            // Create the request
            $hairRequest = new Request([
                'user_id' => $httpRequest->user_id,
                'hair_concerns' => $httpRequest->hair_concerns,
                'status' => 'pending',
            ]);
            $hairRequest->save();

            // Create area selections
            foreach ($httpRequest->area_id as $areaId) {
                $hairRequest->areas()->attach($areaId);
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
                    'image_path' => $imagePath
                ]);
            }

            return response()->json([
                'status' => 201,
                'request' => [
                    'id' => $hairRequest->id,
                    'user_id' => $hairRequest->user_id,
                    'area' => $hairRequest->areas()->get(),
                    'menu' => $hairRequest->menus()->get(),
                    'hair_concerns' => $hairRequest->hair_concerns,
                    'status' => $hairRequest->status,
                    'created_at' => $hairRequest->created_at->toIso8601String(),
                    'updated_at' => $hairRequest->updated_at->toIso8601String(),
                ]
            ], 201);
        } else {
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
                'status' => 200,
                'request_id' => $hairRequest->id,
                'message' => 'Hair stylist request created successfully.',
            ]);
        }
    }

    // ... existing methods ...

    // Method to update a hair stylist request
    public function update(UpdateHairStylistRequest $request, $id): JsonResponse
    {
        // ... existing update method code ...
    }

    // Method to delete an image from a hair stylist request
    public function deleteImage(HttpRequest $httpRequest, $request_id, $image_id): JsonResponse
    {
        // ... existing deleteImage method code ...
    }

    // ... other methods ...
}
