<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreHairStylistRequest;
use App\Models\Request;
use App\Models\RequestArea;
use App\Models\RequestMenu;
use App\Models\Image;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request as HttpRequest;
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
            'area_id' => 'required|array|min:1',
            'menu_id' => 'required|array|min:1',
            'hair_concerns' => 'required|string|max:3000',
            'image_files' => 'nullable|array|max:3',
            'image_files.*' => 'mimes:png,jpg,jpeg|max:5120', // 5MB
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

            foreach ($validated['area_id'] as $areaId) {
                $requestArea = new RequestArea([
                    'request_id' => $hairStylistRequest->id,
                    'area_id' => $areaId,
                ]);
                $requestArea->save();
            }

            foreach ($validated['menu_id'] as $menuId) {
                $requestMenu = new RequestMenu([
                    'request_id' => $hairStylistRequest->id,
                    'menu_id' => $menuId,
                ]);
                $requestMenu->save();
            }

            if (isset($validated['image_files'])) {
                foreach ($validated['image_files'] as $file) {
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

    public function updateHairStylistRequest(StoreHairStylistRequest $request, $id): JsonResponse
    {
        $hairStylistRequest = Request::find($id);
        if (!$hairStylistRequest) {
            return response()->json(['message' => 'Request not found.'], 404);
        }

        $validated = $request->validated();

        $validator = Validator::make($validated, [
            'area_ids' => 'required|array|min:1',
            'menu_ids' => 'required|array|min:1',
            'hair_concerns' => 'required|string|max:3000',
            'images' => 'nullable|array|max:3',
            'images.*' => 'mimes:png,jpg,jpeg|max:5120', // 5MB
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            $hairStylistRequest->fill([
                'hair_concerns' => $validated['hair_concerns'],
            ]);
            $hairStylistRequest->save();

            RequestArea::where('request_id', $hairStylistRequest->id)->delete();
            foreach ($validated['area_ids'] as $areaId) {
                $requestArea = new RequestArea([
                    'request_id' => $hairStylistRequest->id,
                    'area_id' => $areaId,
                ]);
                $requestArea->save();
            }

            RequestMenu::where('request_id', $hairStylistRequest->id)->delete();
            foreach ($validated['menu_ids'] as $menuId) {
                $requestMenu = new RequestMenu([
                    'request_id' => $hairStylistRequest->id,
                    'menu_id' => $menuId,
                ]);
                $requestMenu->save();
            }

            if (isset($validated['images'])) {
                Image::where('request_id', $hairStylistRequest->id)->delete();
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
                'status' => 200,
                'request' => $hairStylistRequest,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to update hair stylist request.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // New method added as per the guideline
    public function validateHairStylistRequest(HttpRequest $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'area_ids' => 'required|array|min:1',
            'menu_ids' => 'required|array|min:1',
            'hair_concerns' => 'sometimes|string|max:3000',
            'images' => 'sometimes|array',
            'images.*' => 'mimes:png,jpg,jpeg|max:5120', // 5MB in kilobytes
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

        // Since there is no additional business logic to perform, we can directly return a success response.
        return response()->json([
            'status' => 200,
            'message' => 'Validation successful.'
        ], 200);
    }
}
