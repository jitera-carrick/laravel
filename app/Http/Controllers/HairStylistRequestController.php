<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreHairStylistRequest;
use App\Models\Request;
use App\Models\RequestArea;
use App\Models\RequestMenu;
use App\Models\Image;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class HairStylistRequestController extends Controller
{
    // ... other methods ...

    public function storeHairStylistRequest(StoreHairStylistRequest $request): JsonResponse
    {
        // ... existing storeHairStylistRequest method ...
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
}
