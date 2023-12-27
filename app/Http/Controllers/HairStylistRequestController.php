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

    public function validateHairStylistRequest(HttpRequest $request): JsonResponse
    {
        // ... existing validateHairStylistRequest method ...
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
