<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreHairStylistRequest;
use App\Http\Requests\UpdateHairStylistRequest; // Import the UpdateHairStylistRequest form request
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

        try {
            DB::beginTransaction();

            $hairStylistRequest = new Request([
                'user_id' => $validated['user_id'],
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

            if (isset($validated['images'])) {
                foreach ($validated['images'] as $file) {
                    $filePath = $file->store('images', 'public');
                    $image = new Image([
                        'request_id' => $hairStylistRequest->id,
                        'file_path' => $filePath,
                        'file_size' => $file->getSize(),
                        'file_format' => $file->getClientOriginalExtension(), // Added file_format from new code
                    ]);
                    $image->save();
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Hair stylist request created successfully.',
                'data' => [
                    'request_id' => $hairStylistRequest->id,
                    'status' => $hairStylistRequest->status,
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
        if (!Auth::check()) {
            return response()->json([
                'message' => 'Unauthorized. User is not authenticated.'
            ], 401);
        }

        try {
            DB::beginTransaction();

            $hairStylistRequest = Request::findOrFail($id);

            if (Auth::id() !== $hairStylistRequest->user_id) {
                return response()->json([
                    'message' => 'Unauthorized to cancel this request.'
                ], 401);
            }

            // Manually delete related entries in the "request_areas", "request_menus", and "images" tables
            RequestArea::where('request_id', $hairStylistRequest->id)->delete();
            RequestMenu::where('request_id', $hairStylistRequest->id)->delete();
            Image::where('request_id', $hairStylistRequest->id)->delete();

            // Utilize Eloquent's `delete` method to remove the request from the "requests" table
            $hairStylistRequest->delete();

            DB::commit();

            return response()->json([
                'message' => 'Request cancelled successfully.'
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Request not found.'
            ], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to cancel hair stylist request.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // ... other methods ...

    public function validateHairStylistRequest(HttpRequest $request): JsonResponse
    {
        // ... existing code for validateHairStylistRequest method ...
    }

    // ... other methods ...

    public function editRequest(UpdateHairStylistRequest $request, $requestId): JsonResponse
    {
        $validated = $request->validated();

        try {
            DB::beginTransaction();

            $hairStylistRequest = Request::where('id', $requestId)
                ->where('user_id', Auth::id())
                ->firstOrFail();

            if (isset($validated['area_ids'])) {
                RequestArea::where('request_id', $hairStylistRequest->id)->delete();
                foreach ($validated['area_ids'] as $areaId) {
                    RequestArea::create([
                        'request_id' => $hairStylistRequest->id,
                        'area_id' => $areaId,
                    ]);
                }
            }

            if (isset($validated['menu_ids'])) {
                RequestMenu::where('request_id', $hairStylistRequest->id)->delete();
                foreach ($validated['menu_ids'] as $menuId) {
                    RequestMenu::create([
                        'request_id' => $hairStylistRequest->id,
                        'menu_id' => $menuId,
                    ]);
                }
            }

            if (isset($validated['hair_concerns'])) {
                $hairStylistRequest->update(['hair_concerns' => $validated['hair_concerns']]);
            }

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
                'message' => 'Hair stylist request updated successfully.',
                'data' => $hairStylistRequest->fresh(),
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to update hair stylist request.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
