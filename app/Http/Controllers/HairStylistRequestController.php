<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreHairStylistRequest;
use App\Models\Request;
use App\Models\RequestArea;
use App\Models\RequestMenu;
use App\Models\Image;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class HairStylistRequestController extends Controller
{
    // ... other methods ...

    public function storeHairStylistRequest(StoreHairStylistRequest $request): JsonResponse
    {
        $validated = $request->validated();

        // Additional validation
        $validator = Validator::make($validated, [
            'user_id' => 'required|exists:users,id,is_logged_in,1',
            'area_ids' => 'required|array|min:1', // Changed from 'area_id' to 'area_ids' to match the requirement
            'menu_ids' => 'required|array|min:1', // Changed from 'menu_id' to 'menu_ids' to match the requirement
            'hair_concerns' => 'required|string|max:3000',
            'images' => 'nullable|array|max:3', // Changed from 'image_files' to 'images' to match the requirement
            'images.*' => 'mimes:png,jpg,jpeg|max:5120', // Changed from 'image_files.*' to 'images.*' and added 'mimes' validation
        ], [
            'area_ids.required' => 'At least one area must be selected.', // Custom error message for 'area_ids'
            'menu_ids.required' => 'At least one menu must be selected.', // Custom error message for 'menu_ids'
            'hair_concerns.max' => 'Hair concerns and requests must be less than 3000 characters.', // Custom error message for 'hair_concerns'
            'images.*.mimes' => 'Invalid image format or size.', // Custom error message for 'images'
            'images.*.max' => 'Invalid image format or size.', // Custom error message for 'images'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            $hairStylistRequest = new Request([
                'user_id' => $validated['user_id'],
                'hair_concerns' => $validated['hair_concerns'],
                'status' => 'pending',
            ]);
            $hairStylistRequest->save();

            foreach ($validated['area_ids'] as $areaId) { // Changed from 'area_id' to 'area_ids' to match the requirement
                $requestArea = new RequestArea([
                    'request_id' => $hairStylistRequest->id,
                    'area_id' => $areaId,
                ]);
                $requestArea->save();
            }

            foreach ($validated['menu_ids'] as $menuId) { // Changed from 'menu_id' to 'menu_ids' to match the requirement
                $requestMenu = new RequestMenu([
                    'request_id' => $hairStylistRequest->id,
                    'menu_id' => $menuId,
                ]);
                $requestMenu->save();
            }

            if (isset($validated['images'])) { // Changed from 'image_files' to 'images' to match the requirement
                foreach ($validated['images'] as $file) {
                    $filePath = $file->store('images', 'public');
                    $image = new Image([
                        'request_id' => $hairStylistRequest->id,
                        'file_path' => $filePath,
                        'file_size' => $file->getSize(),
                    ]);
                    $image->save();
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Hair stylist request created successfully.',
                'data' => $hairStylistRequest,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to create hair stylist request.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // ... other methods ...

    public function cancelHairStylistRequest($id): JsonResponse
    {
        // ... existing cancelHairStylistRequest method ...
    }

    // ... other methods ...

    public function validateHairStylistRequest(HttpRequest $request): JsonResponse
    {
        // ... existing validateHairStylistRequest method ...
    }

    // ... other methods ...
}
