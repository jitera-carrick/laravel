<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateHairStylistRequest;
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
            'area_ids' => 'required|array|min:1',
            'menu_ids' => 'required|array|min:1',
            'hair_concerns' => 'required|string|max:3000',
            'images' => 'required|array|max:3',
            'images.*' => 'image|mimes:jpg,jpeg,png|max:5120', // Validate image format and size
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        DB::beginTransaction();
        try {
            // Update area selections
            RequestAreaSelection::where('request_id', $id)->delete();
            $areaInput = $request->has('area_ids') ? $request->area_ids : $request->area; // Support both 'area_ids' and 'area'
            foreach ($areaInput as $areaId) {
                RequestAreaSelection::create([
                    'request_id' => $id,
                    'area_id' => $areaId,
                ]);
            }

            // Update menu selections
            RequestMenuSelection::where('request_id', $id)->delete();
            $menuInput = $request->has('menu_ids') ? $request->menu_ids : $request->menu; // Support both 'menu_ids' and 'menu'
            foreach ($menuInput as $menuId) {
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
                // Use the store method if the image is an uploaded file, otherwise use Storage facade
                if (is_uploaded_file($image)) {
                    $imagePath = $image->store('request_images', 'public');
                } else {
                    $imagePath = Storage::disk('public')->put('request_images', $image);
                }
                RequestImage::create([
                    'request_id' => $id,
                    'image_path' => $imagePath,
                ]);
            }

            DB::commit();
            return response()->json([
                'status' => 200,
                'request' => $hairRequest->fresh(),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'An unexpected error occurred.'], 500);
        }
    }

    // ... other methods ...
}
