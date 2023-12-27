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
        $validated = $request->validated();

        // Additional validation
        $validator = Validator::make($validated, [
            'user_id' => 'required|exists:users,id,is_logged_in,1',
            'area_ids' => 'required|array|min:1',
            'menu_ids' => 'required|array|min:1',
            'hair_concerns' => 'required|string|max:3000',
            'images' => 'nullable|array|max:3',
            'images.*' => 'mimes:png,jpg,jpeg|max:5120',
        ], [
            'area_ids.required' => 'At least one area must be selected.',
            'menu_ids.required' => 'At least one menu must be selected.',
            'hair_concerns.max' => 'Hair concerns and requests must be less than 3000 characters.',
            'images.*.mimes' => 'Invalid image format or size.',
            'images.*.max' => 'Invalid image format or size.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        if (!Auth::check()) {
            return response()->json([
                'message' => 'Unauthorized. User is not authenticated.'
            ], 401);
        }

        try {
            DB::beginTransaction();

            $hairStylistRequest = new Request([
                'user_id' => Auth::id(), // Use authenticated user's ID instead of passing it in the request
                'hair_concerns' => $validated['hair_concerns'],
                'status' => 'pending',
            ]);
            $hairStylistRequest->save();

            foreach ($validated['area_ids'] as $areaId) {
                $requestArea = new RequestArea([
                    'request_id' => $hairStylistRequest->id,
                    'area_id' => $areaId,
                ]);
                $requestArea->save();
            }

            foreach ($validated['menu_ids'] as $menuId) {
                $requestMenu = new RequestMenu([
                    'request_id' => $hairStylistRequest->id,
                    'menu_id' => $menuId,
                ]);
                $requestMenu->save();
            }

            $imageData = [];
            if (isset($validated['images'])) {
                foreach ($validated['images'] as $file) {
                    $filePath = $file->store('images', 'public');
                    $image = new Image([
                        'request_id' => $hairStylistRequest->id,
                        'file_path' => $filePath,
                        'file_size' => $file->getSize(),
                        'file_format' => $file->getClientOriginalExtension(),
                    ]);
                    $image->save();
                    $imageData[] = [
                        'file_path' => $filePath,
                        'file_size' => round($file->getSize() / 1024 / 1024, 2), // Convert to MB
                    ];
                }
            }

            DB::commit();

            return response()->json([
                'status' => 201,
                'request' => [
                    'id' => $hairStylistRequest->id,
                    'area_ids' => $validated['area_ids'],
                    'menu_ids' => $validated['menu_ids'],
                    'hair_concerns' => $validated['hair_concerns'],
                    'images' => $imageData,
                    'created_at' => $hairStylistRequest->created_at->toIso8601String(),
                ],
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

    // ... other methods ...
}
