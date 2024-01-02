<?php

namespace App\Http\Controllers;

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

    // Method to create a new hair stylist request
    public function createHairStylistRequest(CreateHairStylistRequest $request): JsonResponse
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'area_ids' => 'required|array|min:1|exists:areas,id',
            'menu_ids' => 'required|array|min:1|exists:menus,id',
            'hair_concerns' => 'required|string|max:3000',
            'images' => 'sometimes|array|max:3',
            'images.*' => 'image|mimes:png,jpg,jpeg|max:5120', // 5MB
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        // Start a transaction in case any part of the request creation fails
        DB::beginTransaction();
        try {
            // Create a new hair stylist request record in the database
            $hairRequest = Request::create([
                'user_id' => Auth::id(),
                'hair_concerns' => $request->hair_concerns,
                'status' => 'pending', // Assuming 'pending' is a valid status
            ]);

            // Attach area and menu selections
            $hairRequest->requestAreas()->sync($request->area_ids);
            $hairRequest->requestMenus()->sync($request->menu_ids);

            // Handle the uploading of images
            if ($request->has('images')) {
                foreach ($request->images as $image) {
                    // Store the image and get the path
                    $imagePath = Storage::disk('public')->put('request_images', $image);
                    // Create records in the RequestImage model with the paths of the uploaded images
                    RequestImage::create([
                        'request_id' => $hairRequest->id,
                        'image_path' => $imagePath,
                    ]);
                }
            }

            DB::commit();

            // Return a JSON response with a 201 status code and the newly created request data
            return response()->json([
                'status' => 201,
                'request' => $hairRequest->fresh('requestAreas', 'requestMenus', 'requestImages'),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to create hair stylist request.'], 500);
        }
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
            'area' => 'required|array|min:1',
            'menu' => 'required|array|min:1',
            'hair_concerns' => 'required|string|max:3000',
            'images' => 'sometimes|array|min:1',
            'images.*' => 'image|mimes:png,jpg,jpeg|max:5120', // 5MB
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        // Update area selections
        $hairRequest->requestAreas()->sync($request->area);
        // Update menu selections
        $hairRequest->requestMenus()->sync($request->menu);

        // Update hair concerns
        $hairRequest->update(['hair_concerns' => $request->hair_concerns]);

        // Update images
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
        }

        return response()->json([
            'status' => 200,
            'request' => $hairRequest->fresh('requestAreas', 'requestMenus', 'requestImages'),
        ]);
    }

    // ... other methods ...
}
