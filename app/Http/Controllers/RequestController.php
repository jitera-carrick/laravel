<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateHairStylistRequest;
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
use Illuminate\Support\Str;

class RequestController extends Controller
{
    // ... other methods ...

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
            'area_ids' => 'required|array|min:1',
            'area_ids.*' => 'exists:areas,id',
            'menu_ids' => 'required|array|min:1',
            'menu_ids.*' => 'exists:menus,id',
            'hair_concerns' => 'required|string|max:3000',
            'image_paths' => 'required|array|min:1',
            'image_paths.*' => 'image|mimes:png,jpg,jpeg|max:5120', // 5MB
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        // Update area selections
        RequestAreaSelection::where('request_id', $id)->delete();
        foreach ($request->area_ids as $areaId) {
            RequestAreaSelection::create([
                'request_id' => $id,
                'area_id' => $areaId,
            ]);
        }

        // Update menu selections
        RequestMenuSelection::where('request_id', $id)->delete();
        foreach ($request->menu_ids as $menuId) {
            RequestMenuSelection::create([
                'request_id' => $id,
                'menu_id' => $menuId,
            ]);
        }

        // Update hair concerns
        $hairRequest->update(['hair_concerns' => $request->hair_concerns]);

        // Update images
        RequestImage::where('request_id', $id)->delete();
        foreach ($request->image_paths as $image) {
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

    // Method to create a hair stylist request
    public function createHairStylistRequest(\Illuminate\Http\Request $request): JsonResponse
    {
        $user = Auth::user();

        // Validate the request
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'area_ids' => 'required|array|min:1',
            'area_ids.*' => 'exists:areas,id',
            'menu_ids' => 'required|array|min:1',
            'menu_ids.*' => 'exists:menus,id',
            'hair_concerns' => 'required|string|max:3000',
            'image_paths' => 'required|array|max:3',
            'image_paths.*' => 'image|mimes:png,jpg,jpeg|max:5120', // 5MB
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        if ($user->id != $request->user_id) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        try {
            // Create the request
            $hairRequest = Request::create([
                'user_id' => $request->user_id,
                'hair_concerns' => $request->hair_concerns,
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Create area selections
            foreach ($request->area_ids as $areaId) {
                RequestAreaSelection::create([
                    'request_id' => $hairRequest->id,
                    'area_id' => $areaId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Create menu selections
            foreach ($request->menu_ids as $menuId) {
                RequestMenuSelection::create([
                    'request_id' => $hairRequest->id,
                    'menu_id' => $menuId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Create images
            foreach ($request->image_paths as $imagePath) {
                // Store the image and get the path
                $imagePath = Storage::disk('public')->put('request_images', $imagePath);
                RequestImage::create([
                    'request_id' => $hairRequest->id,
                    'image_path' => $imagePath,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            return response()->json([
                'status' => 200,
                'request' => $hairRequest->load('requestAreaSelections.area', 'requestMenuSelections.menu', 'requestImages'),
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to create request.'], 500);
        }
    }

    // ... other methods ...
}
