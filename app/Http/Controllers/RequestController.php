<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateHairStylistRequest;
use App\Http\Requests\CreateHairStylistRequest;
use App\Models\Request;
use App\Models\RequestAreaSelection;
use App\Models\RequestMenuSelection;
use App\Models\RequestImage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request as HttpRequest;

class RequestController extends Controller
{
    // ... other methods ...

    // Method to create a new hair stylist request
    public function createHairStylistRequest(CreateHairStylistRequest $request): JsonResponse
    {
        // ... createHairStylistRequest method code ...
    }

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
     * Delete a request image.
     *
     * @param HttpRequest $request
     * @param int $image_id The ID of the image to delete.
     * @return JsonResponse
     */
    public function deleteRequestImage(HttpRequest $request, int $image_id): JsonResponse
    {
        $validator = Validator::make(['image_id' => $image_id], [
            'image_id' => 'required|integer|exists:request_images,id',
        ]);

        if ($validator->fails()) {
            $errorMessage = $validator->errors()->first();
            $errorCode = $errorMessage === 'The selected image id is invalid.' ? 404 : 400;
            return response()->json(['message' => $errorMessage], $errorCode);
        }

        try {
            $user = Auth::user();
            $requestImage = RequestImage::find($image_id);

            if (!$requestImage || $requestImage->request->user_id !== $user->id) {
                return response()->json(['message' => 'Image not found or unauthorized.'], 404);
            }

            // Delete the image
            Storage::disk('public')->delete($requestImage->image_path);
            $requestImage->delete();

            return response()->json(['status' => 200, 'message' => 'Image deleted successfully.'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An unexpected error occurred.'], 500);
        }
    }

    // ... other methods ...
}
