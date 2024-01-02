<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateHairStylistRequest;
use App\Http\Requests\CreateHairStylistRequest;
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
            'images' => 'required|array|min:1',
            'images.*' => 'image|mimes:png,jpg,jpeg|max:5120', // 5MB
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        // Update area selections
        RequestAreaSelection::where('request_id', $id)->delete();
        foreach ($request->area as $areaId) {
            RequestAreaSelection::create([
                'request_id' => $id,
                'area_id' => $areaId,
            ]);
        }

        // Update menu selections
        RequestMenuSelection::where('request_id', $id)->delete();
        foreach ($request->menu as $menuId) {
            RequestMenuSelection::create([
                'request_id' => $id,
                'menu_id' => $menuId,
            ]);
        }

        // Update hair concerns
        $hairRequest->update(['hair_concerns' => $request->hair_concerns]);

        // Update images
        RequestImage::where('request_id', $id)->delete();
        foreach ($request->images as $image) {
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
    public function createHairStylistRequest(CreateHairStylistRequest $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'area_ids' => 'required|array|min:1|exists:areas,id',
            'menu_ids' => 'required|array|min:1|exists:menus,id',
            'hair_concerns' => 'required|string|max:3000',
            'image_paths' => 'required|array|max:3',
            'image_paths.*' => 'string|distinct|regex:/^(.*\.(?!(png|jpg|jpeg)$))?[^.]*$/i|max:5120', // Assuming the image paths are validated elsewhere
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        try {
            DB::beginTransaction();

            $user = Auth::user();
            if ($user->id != $request->user_id) {
                return response()->json(['message' => 'Unauthorized.'], 401);
            }

            $hairRequest = new Request([
                'hair_concerns' => $request->hair_concerns,
                'user_id' => $user->id,
                'status' => 'new', // Assuming 'new' is a valid status
            ]);
            $hairRequest->save();

            foreach ($request->area_ids as $areaId) {
                RequestAreaSelection::create([
                    'request_id' => $hairRequest->id,
                    'area_id' => $areaId,
                ]);
            }

            foreach ($request->menu_ids as $menuId) {
                RequestMenuSelection::create([
                    'request_id' => $hairRequest->id,
                    'menu_id' => $menuId,
                ]);
            }

            foreach ($request->image_paths as $imagePath) {
                // Store the image and get the path if it's a file instance
                if ($imagePath instanceof \Illuminate\Http\UploadedFile) {
                    $storedImagePath = Storage::disk('public')->put('request_images', $imagePath);
                } else {
                    $storedImagePath = $imagePath; // Assuming the image path is already a stored file path
                }
                RequestImage::create([
                    'request_id' => $hairRequest->id,
                    'image_path' => $storedImagePath,
                ]);
            }

            DB::commit();

            return response()->json([
                'status' => 200,
                'message' => 'Hair stylist request created successfully.',
                'request_id' => $hairRequest->id,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 500,
                'message' => 'An error occurred while creating the request: ' . $e->getMessage(),
            ], 500);
        }
    }

    // ... other methods ...
}
