<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateHairStylistRequest;
use App\Models\Request;
use App\Models\RequestAreaSelection;
use App\Models\RequestMenuSelection;
use App\Models\RequestImage;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request as HttpRequest;

class HairStylistRequestController extends Controller
{
    // ... other methods ...

    public function update(HttpRequest $httpRequest, $id): JsonResponse
    {
        $validator = Validator::make($httpRequest->all(), [
            'area' => 'required|array|min:1',
            'menu' => 'required|array|min:1',
            'hair_concerns' => 'nullable|string|max:3000',
            'images.*' => 'nullable|image|mimes:png,jpg,jpeg|max:5120', // 5MB
        ], [
            'area.required' => 'Area selection is required.',
            'menu.required' => 'Menu selection is required.',
            'hair_concerns.max' => 'Hair concerns and requests must be less than 3000 characters.',
            'images.*.image' => 'Invalid image format or size.',
            'images.*.mimes' => 'Invalid image format or size.',
            'images.*.max' => 'Invalid image format or size.',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        try {
            $hairStylistRequest = Request::findOrFail($id);

            // Check if the authenticated user is authorized to update the request
            if (Auth::id() !== $hairStylistRequest->user_id) {
                return response()->json(['message' => 'Unauthorized to update this request.'], 403);
            }

            // Update 'area' and 'menu' by removing existing entries and creating new ones
            RequestAreaSelection::where('request_id', $hairStylistRequest->id)->delete();
            RequestMenuSelection::where('request_id', $hairStylistRequest->id)->delete();

            foreach ($httpRequest->area as $areaId) {
                RequestAreaSelection::create([
                    'request_id' => $hairStylistRequest->id,
                    'area_id' => $areaId
                ]);
            }

            foreach ($httpRequest->menu as $menuId) {
                RequestMenuSelection::create([
                    'request_id' => $hairStylistRequest->id,
                    'menu_id' => $menuId
                ]);
            }

            // Update 'hair_concerns'
            $hairStylistRequest->hair_concerns = $httpRequest->hair_concerns;
            $hairStylistRequest->save();

            // Handle 'images'
            if ($httpRequest->has('images')) {
                foreach ($httpRequest->images as $image) {
                    // Assuming there is a method to handle file uploads and return the file path
                    $imagePath = $this->uploadImage($image); // Placeholder for actual upload logic
                    RequestImage::create([
                        'request_id' => $hairStylistRequest->id,
                        'image_path' => $imagePath
                    ]);
                }
            }

            return response()->json($hairStylistRequest->fresh(), 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Request not found.'], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred while updating the request.'], 500);
        }
    }

    // Placeholder for the image upload logic
    private function uploadImage($image)
    {
        // ... upload logic ...
        return 'path/to/image.jpg'; // Example return value
    }
}
