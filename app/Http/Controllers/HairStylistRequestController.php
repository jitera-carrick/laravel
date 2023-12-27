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
use Carbon\Carbon; // Import Carbon for date handling

class HairStylistRequestController extends Controller
{
    // ... other methods ...

    // Add the new autoExpireRequests method
    public function autoExpireRequests(): JsonResponse
    {
        // ... existing autoExpireRequests method ...
    }

    public function storeHairStylistRequest(StoreHairStylistRequest $request): JsonResponse
    {
        // ... existing storeHairStylistRequest method ...
    }

    // ... other methods ...

    public function cancelHairStylistRequest($id): JsonResponse
    {
        // ... existing cancelHairStylistRequest method ...
    }

    // ... other methods ...

    // This method has been renamed from validateHairStylistRequest to validateHairStylistRequestInput to resolve the conflict
    public function validateHairStylistRequestInput(HttpRequest $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'area_ids' => 'required|array|min:1',
                'menu_ids' => 'required|array|min:1',
                'hair_concerns' => 'nullable|string|max:3000',
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
                    'status' => 422,
                    'errors' => $validator->errors(),
                ], 422);
            }

            return response()->json([
                'status' => 200,
                'message' => 'Validation successful.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'error' => 'An unexpected error occurred during validation.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // ... other methods ...

    public function editRequest(UpdateHairStylistRequest $request, $requestId): JsonResponse
    {
        // ... existing editRequest method ...
    }

    // ... other methods ...

    // Update the updateHairStylistRequest method to meet the requirements
    public function updateHairStylistRequest(UpdateHairStylistRequest $request, $id): JsonResponse
    {
        if (!Auth::check()) {
            return response()->json([
                'message' => 'Unauthorized. User is not authenticated.'
            ], 401);
        }

        $validated = $request->validated();

        try {
            DB::beginTransaction();

            $hairStylistRequest = Request::where('id', $id)
                ->where('user_id', Auth::id())
                ->firstOrFail();

            // Update areas
            if (isset($validated['area_ids'])) {
                RequestArea::where('request_id', $hairStylistRequest->id)->delete();
                foreach ($validated['area_ids'] as $areaId) {
                    RequestArea::create([
                        'request_id' => $hairStylistRequest->id,
                        'area_id' => $areaId,
                    ]);
                }
            }

            // Update menus
            if (isset($validated['menu_ids'])) {
                RequestMenu::where('request_id', $hairStylistRequest->id)->delete();
                foreach ($validated['menu_ids'] as $menuId) {
                    RequestMenu::create([
                        'request_id' => $hairStylistRequest->id,
                        'menu_id' => $menuId,
                    ]);
                }
            }

            // Update hair concerns
            if (isset($validated['hair_concerns'])) {
                $hairStylistRequest->update(['hair_concerns' => $validated['hair_concerns']]);
            }

            // Update images
            if (isset($validated['images'])) {
                $existingImages = Image::where('request_id', $hairStylistRequest->id)->get();
                foreach ($existingImages as $existingImage) {
                    Storage::delete($existingImage->file_path);
                    $existingImage->delete();
                }

                foreach ($validated['images'] as $file) {
                    $filePath = $file->store('images', 'public');
                    Image::create([
                        'request_id' => $hairStylistRequest->id,
                        'file_path' => $filePath,
                        'file_size' => $file->getSize(),
                        'file_format' => $file->getClientOriginalExtension(),
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'status' => 200,
                'request' => $hairStylistRequest->fresh(),
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to update hair stylist request.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // ... other methods ...
}
