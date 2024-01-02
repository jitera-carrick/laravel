<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateHairStylistRequest;
use App\Http\Requests\UpdateHairStylistRequest;
use App\Models\Request;
use App\Models\RequestArea;
use App\Models\RequestMenu;
use App\Models\RequestImage;
use App\Models\Area;
use App\Models\Menu;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class RequestController extends Controller
{
    // ... other methods ...

    // Method to create a new hair stylist request
    public function createHairStylistRequest(CreateHairStylistRequest $request): JsonResponse
    {
        // The CreateHairStylistRequest form request class already handles validation, so we don't need the Validator here.
        $validated = $request->validated();
        $user = Auth::user();

        if ($user->id !== $validated['user_id']) {
            return response()->json(['message' => 'Unauthorized user.'], 403);
        }

        // Start a transaction in case any part of the request creation fails
        DB::beginTransaction();
        try {
            // Create a new Request model instance
            $hairRequest = new Request([
                'user_id' => $user->id,
                'hair_concerns' => $validated['hair_concerns'],
                'status' => 'pending', // Assuming 'pending' is the default status
            ]);
            $hairRequest->save();

            // Iterate over area_ids and create new entries in the request_areas table
            foreach ($validated['area_ids'] as $areaId) {
                RequestArea::create([
                    'request_id' => $hairRequest->id,
                    'area_id' => $areaId,
                ]);
            }

            // Iterate over menu_ids and create new entries in the request_menus table
            foreach ($validated['menu_ids'] as $menuId) {
                RequestMenu::create([
                    'request_id' => $hairRequest->id,
                    'menu_id' => $menuId,
                ]);
            }

            // Iterate over image_paths, validate and store the images, and create new entries in the request_images table
            if (isset($validated['image_paths'])) {
                foreach ($validated['image_paths'] as $imagePath) {
                    // Store the image and get the path
                    $storedImagePath = Storage::disk('public')->put('request_images', $imagePath);
                    RequestImage::create([
                        'request_id' => $hairRequest->id,
                        'image_path' => $storedImagePath,
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

        // The UpdateHairStylistRequest form request class already handles validation, so we don't need the Validator here.
        $validated = $request->validated();

        // Update area selections
        RequestArea::where('request_id', $id)->delete();
        foreach ($validated['area'] as $areaId) {
            RequestArea::create([
                'request_id' => $id,
                'area_id' => $areaId,
            ]);
        }

        // Update menu selections
        RequestMenu::where('request_id', $id)->delete();
        foreach ($validated['menu'] as $menuId) {
            RequestMenu::create([
                'request_id' => $id,
                'menu_id' => $menuId,
            ]);
        }

        // Update hair concerns
        $hairRequest->update(['hair_concerns' => $validated['hair_concerns']]);

        // Update images
        RequestImage::where('request_id', $id)->delete();
        if (isset($validated['images'])) {
            foreach ($validated['images'] as $image) {
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
