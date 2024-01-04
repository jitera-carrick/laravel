<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request as HttpRequest;
use App\Http\Requests\UpdateHairStylistRequest;
use App\Http\Requests\CreateHairStylistRequest;
use App\Models\User;
use App\Models\Request;
use App\Models\RequestAreaSelection;
use App\Models\RequestMenuSelection;
use App\Models\RequestImage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class RequestController extends Controller
{
    // ... other methods ...

    // Method to create a hair stylist request
    public function createHairStylistRequest(CreateHairStylistRequest $httpRequest): JsonResponse
    {
        $user = Auth::user();

        // Validate the request
        $validator = Validator::make($httpRequest->all(), [
            'user_id' => 'required|exists:users,id',
            'area_id' => 'required|array|min:1',
            'menu_id' => 'required|array|min:1',
            'hair_concerns' => 'required|string|max:3000',
            'image_path' => 'required|array|max:3',
            'image_path.*' => 'file|image|mimes:png,jpg,jpeg|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        if ($httpRequest->user_id != $user->id) {
            return response()->json(['message' => 'Unauthorized user.'], 401);
        }

        // Create the request
        $hairRequest = Request::create([
            'user_id' => $httpRequest->user_id,
            'hair_concerns' => $httpRequest->hair_concerns,
            'status' => 'pending',
        ]);

        // Create area selections
        foreach ($httpRequest->area_id as $areaId) {
            RequestAreaSelection::create([
                'request_id' => $hairRequest->id,
                'area_id' => $areaId,
            ]);
        }

        // Create menu selections
        foreach ($httpRequest->menu_id as $menuId) {
            RequestMenuSelection::create([
                'request_id' => $hairRequest->id,
                'menu_id' => $menuId,
            ]);
        }

        // Create images
        foreach ($httpRequest->image_path as $image) {
            $imagePath = Storage::disk('public')->put('request_images', $image);
            RequestImage::create([
                'request_id' => $hairRequest->id,
                'image_path' => $imagePath,
            ]);
        }

        return response()->json([
            'status' => 201,
            'request' => [
                'id' => $hairRequest->id,
                'area_id' => $httpRequest->area_id,
                'menu_id' => $httpRequest->menu_id,
                'hair_concerns' => $httpRequest->hair_concerns,
                'status' => 'pending',
                'created_at' => $hairRequest->created_at->toIso8601String(),
                'user_id' => $user->id,
            ],
        ], 201);
    }

    // Method to update a hair stylist request
    public function updateHairStylistRequest(UpdateHairStylistRequest $httpRequest, $id): JsonResponse
    {
        $hairRequest = Request::find($id);
        $user = Auth::user();

        if (!$hairRequest || $hairRequest->user_id !== $user->id) {
            return response()->json(['message' => 'Request not found or unauthorized.'], 404);
        }

        $validator = Validator::make($httpRequest->all(), [
            'area' => 'sometimes|array|min:1',
            'menu' => 'sometimes|array|min:1',
            'hair_concerns' => 'sometimes|string|max:3000',
            'images' => 'sometimes|array|min:1|max:3',
            'images.*' => 'image|mimes:png,jpg,jpeg|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $validated = $validator->validated();

        DB::transaction(function () use ($validated, $hairRequest, $id) {
            if (isset($validated['area'])) {
                RequestAreaSelection::where('request_id', $id)->delete();
                foreach ($validated['area'] as $areaId) {
                    RequestAreaSelection::create([
                        'request_id' => $id,
                        'area_id' => $areaId,
                    ]);
                }
            }

            if (isset($validated['menu'])) {
                RequestMenuSelection::where('request_id', $id)->delete();
                foreach ($validated['menu'] as $menuId) {
                    RequestMenuSelection::create([
                        'request_id' => $id,
                        'menu_id' => $menuId,
                    ]);
                }
            }

            if (isset($validated['hair_concerns'])) {
                $hairRequest->update(['hair_concerns' => $validated['hair_concerns']]);
            }

            if (isset($validated['images'])) {
                RequestImage::where('request_id', $id)->delete();
                foreach ($validated['images'] as $image) {
                    $imagePath = Storage::disk('public')->put('request_images', $image);
                    RequestImage::create([
                        'request_id' => $id,
                        'image_path' => $imagePath,
                    ]);
                }
            }
        });

        return response()->json([
            'status' => 200,
            'request' => $hairRequest->fresh(),
        ]);
    }

    // Method to delete an image from a hair stylist request
    public function deleteRequestImage(int $request_id, int $image_id): JsonResponse
    {
        try {
            $user = Auth::user();

            $request = Request::findOrFail($request_id);
            $image = RequestImage::where('request_id', $request_id)->findOrFail($image_id);

            if ($user->id !== $request->user_id) {
                return response()->json(['message' => 'Unauthorized user.'], 403);
            }

            DB::beginTransaction();

            // Delete the image
            Storage::disk('public')->delete($image->image_path);
            $image->delete();

            // Update the request's updated_at timestamp
            $request->touch();

            DB::commit();

            return response()->json(['message' => 'Image deleted successfully.'], 200);
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json(['message' => 'Request or image not found.'], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'An error occurred while deleting the image.'], 500);
        }
    }

    // ... other methods ...
}
