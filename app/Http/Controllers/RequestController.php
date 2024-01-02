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
            'area' => 'required|array|min:1',
            'menu' => 'required|array|min:1',
            'hair_concerns' => 'required|string|max:3000',
            'images' => 'required|array|min:1',
            'images.*' => 'image|mimes:png,jpg,jpeg|max:5120', // 5MB
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        // Update area selections
        RequestAreaSelection::where('request_id', $id)->delete();
        foreach ($request->area as $areaId) {
            RequestAreaSelection::create([
                'request_id' => $id,
                'area_id' => $areaId,
            ]);
        }

        // Update menu selections
        RequestMenuSelection::where('request_id', $id)->delete();
        foreach ($request->menu as $menuId) {
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
            // Store the image and get the path
            $imagePath = Storage::disk('public')->put('request_images', $image);
            RequestImage::create([
                'request_id' => $id,
                'image_path' => $imagePath,
            ]);
        }

        return response()->json([
            'status' => 200,
            'request' => $hairRequest->fresh(),
        ]);
    }

    /**
     * Delete a specific image from a hair stylist request.
     *
     * @param int $request_id The ID of the request.
     * @param string $image_path The path of the image to delete.
     * @return JsonResponse
     */
    public function deleteImage($request_id, $image_path): JsonResponse
    {
        // Find the request by ID
        $hairRequest = Request::find($request_id);

        // If the request does not exist, return a 404 response
        if (!$hairRequest) {
            return response()->json(['message' => 'Request not found.'], 404);
        }

        // Find the image by request ID and image path
        $requestImage = RequestImage::where('request_id', $request_id)
                                    ->where('image_path', $image_path)
                                    ->first();

        // If the image does not exist, return a 404 response
        if (!$requestImage) {
            return response()->json(['message' => 'Image not found.'], 404);
        }

        // Delete the image file from storage
        if (Storage::disk('public')->exists($image_path)) {
            Storage::disk('public')->delete($image_path);
        }

        // Delete the image entry from the database
        $requestImage->delete();

        // Return a 200 response indicating the image was deleted successfully
        return response()->json([
            'message' => 'Image deleted successfully.',
        ], 200);
    }

    // ... other methods ...
}
