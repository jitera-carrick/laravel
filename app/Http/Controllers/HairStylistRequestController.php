<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreHairStylistRequest;
use App\Http\Requests\UpdateHairStylistRequest;
use App\Models\Request;
use App\Models\RequestArea;
use App\Models\RequestMenu;
use App\Models\Image;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class HairStylistRequestController extends Controller
{
    // ... other methods ...

    public function storeHairStylistRequest(StoreHairStylistRequest $request): JsonResponse
    {
        // The existing storeHairStylistRequest method is kept as it is.
        // ... existing code ...
    }

    // ... other methods ...

    public function cancelHairStylistRequest($id): JsonResponse
    {
        // The existing cancelHairStylistRequest method is kept as it is.
        // ... existing code ...
    }

    // ... other methods ...

    public function validateHairStylistRequest(HttpRequest $request): JsonResponse
    {
        // The existing validateHairStylistRequest method is kept as it is.
        // ... existing code ...
    }

    // ... other methods ...

    public function editRequest(UpdateHairStylistRequest $request, $requestId): JsonResponse
    {
        // The existing editRequest method is kept as it is.
        // ... existing code ...
    }

    // New method createHairStylistRequest is added to meet the requirement
    public function createHairStylistRequest(HttpRequest $request): JsonResponse
    {
        if (!Auth::check()) {
            return response()->json([
                'message' => 'Unauthorized. User is not authenticated.'
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'area_ids' => 'required|array|min:1',
            'menu_ids' => 'required|array|min:1',
            'hair_concerns' => 'required|string|max:3000',
            'images' => 'nullable|array|max:3',
            'images.*' => 'mimes:png,jpg,jpeg|max:5120',
        ], [
            'area_ids.required' => 'Please select at least one area.',
            'menu_ids.required' => 'Please select at least one menu.',
            'hair_concerns.max' => 'Hair concerns text is too long.',
            'images.*.mimes' => 'Invalid image format or size.',
            'images.*.max' => 'Invalid image format or size.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            $user = Auth::user();
            $hairStylistRequest = new Request([
                'user_id' => $user->id,
                'hair_concerns' => $request->input('hair_concerns'),
                'status' => 'pending',
            ]);
            $hairStylistRequest->save();

            foreach ($request->input('area_ids') as $areaId) {
                $requestArea = new RequestArea([
                    'request_id' => $hairStylistRequest->id,
                    'area_id' => $areaId,
                ]);
                $requestArea->save();
            }

            foreach ($request->input('menu_ids') as $menuId) {
                $requestMenu = new RequestMenu([
                    'request_id' => $hairStylistRequest->id,
                    'menu_id' => $menuId,
                ]);
                $requestMenu->save();
            }

            if ($request->has('images')) {
                foreach ($request->file('images') as $file) {
                    $filePath = $file->store('images', 'public');
                    $image = new Image([
                        'request_id' => $hairStylistRequest->id,
                        'file_path' => $filePath,
                        'file_size' => $file->getSize(),
                        'file_format' => $file->getClientOriginalExtension(),
                    ]);
                    $image->save();
                }
            }

            DB::commit();

            return response()->json([
                'status' => 201,
                'request' => $hairStylistRequest->fresh(['requestAreas', 'requestMenus', 'images']),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to create hair stylist request.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
