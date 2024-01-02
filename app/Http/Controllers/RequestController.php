<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateHairStylistRequest;
use App\Http\Requests\CreateHairStylistRequest; // Import the CreateHairStylistRequest form request class
use App\Models\Request;
use App\Models\RequestAreaSelection;
use App\Models\RequestMenuSelection;
use App\Models\RequestImage;
use App\Models\Area;
use App\Models\Menu;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class RequestController extends Controller
{
    // ... other methods ...

    // Method to create a new hair stylist request
    public function createHairStylistRequest(CreateHairStylistRequest $request): JsonResponse
    {
        // ... createHairStylistRequest method code ...
    }

    // Method to update a hair stylist request
    public function update(UpdateHairStylistRequest $request, $id): JsonResponse
    {
        $hairRequest = Request::find($id);
        $user = Auth::user();

        if (!$hairRequest || $hairRequest->user_id !== $user->id) {
            return response()->json(['message' => 'Request not found or unauthorized.'], 404);
        }

        // Validate the request
        $validator = Validator::make($request->all(), [
            'area_ids' => 'required|array|min:1|exists:areas,id',
            'menu_ids' => 'required|array|min:1|exists:menus,id',
            'hair_concerns' => 'required|string|max:3000',
            'images' => 'sometimes|array|max:3',
            'images.*' => 'image|mimes:png,jpg,jpeg|max:5120', // 5MB
            'image_paths' => 'sometimes|array|max:3',
            'image_paths.*' => 'string', // Additional image format and file size validation can be added here
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        // Start a transaction in case any part of the request update fails
        DB::beginTransaction();
        try {
            // Update area selections
            $hairRequest->requestAreas()->sync($request->area_ids ?? []);

            // Update menu selections
            $hairRequest->requestMenus()->sync($request->menu_ids ?? []);

            // Update hair concerns
            $hairRequest->update(['hair_concerns' => $request->hair_concerns]);

            // Update images if they are provided
            if ($request->has('images')) {
                RequestImage::where('request_id', $id)->delete();
                foreach ($request->images as $image) {
                    // Store the image and get the path
                    $imagePath = Storage::disk('public')->put('request_images', $image);
                    RequestImage::create([
                        'request_id' => $id,
                        'image_path' => $imagePath,
                    ]);
                }
            } elseif ($request->has('image_paths')) {
                RequestImage::where('request_id', $id)->delete();
                foreach ($request->image_paths as $imagePath) {
                    // Assuming the image already exists in storage
                    RequestImage::create([
                        'request_id' => $id,
                        'image_path' => $imagePath,
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'status' => 200,
                'request' => $hairRequest->fresh('requestAreas', 'requestMenus', 'requestImages'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to update hair stylist request.'], 500);
        }
    }

    // ... other methods ...
}
