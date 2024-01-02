<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateHairStylistRequest;
use App\Models\Request;
use App\Models\RequestArea;
use App\Models\RequestMenu;
use App\Models\RequestImage;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request as HttpRequest;

class RequestController extends Controller
{
    // ... other methods ...

    // Method to store a hair stylist request
    public function store(HttpRequest $httpRequest): JsonResponse
    {
        $validator = Validator::make($httpRequest->all(), [
            'area' => 'required|array|min:1',
            'menu' => 'required|array|min:1',
            'hair_concerns' => 'nullable|string|max:3000',
            'images.*' => 'nullable|image|mimes:png,jpg,jpeg|max:5120',
        ], [
            'area.required' => 'Area selection is required.',
            'menu.required' => 'Menu selection is required.',
            'hair_concerns.max' => 'Hair concerns and requests must be less than 3000 characters.',
            'images.*.image' => 'Invalid image format or size.',
            'images.*.mimes' => 'Invalid image format or size.',
            'images.*.max' => 'Invalid image format or size.',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $validated = $validator->validated();
            $newRequest = new Request($validated);
            $newRequest->status = 'Pending'; // Assuming 'status' is a field in the Request model
            $newRequest->user_id = Auth::id(); // Set the user_id to the authenticated user's ID
            $newRequest->save();

            foreach ($validated['area'] as $areaId) {
                $newRequest->requestAreaSelections()->create(['area_id' => $areaId]);
            }

            foreach ($validated['menu'] as $menuId) {
                $newRequest->requestMenuSelections()->create(['menu_id' => $menuId]);
            }

            // Handle image uploads and association with the request here...
            if (isset($validated['images'])) {
                foreach ($validated['images'] as $image) {
                    // Assuming there is a method to handle the file upload and return the path
                    $imagePath = $this->uploadImage($image);
                    $newRequest->requestImages()->create(['image_path' => $imagePath]);
                }
            }

            return response()->json([
                'status' => 201,
                'request' => $newRequest->load('requestAreaSelections', 'requestMenuSelections', 'requestImages'),
            ], 201);
        } catch (\Exception $e) {
            // Handle exceptions and return an appropriate error response
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // ... other methods ...

    // Method to upload an image and return the path
    protected function uploadImage($image)
    {
        // Assuming there is a storage disk configured for images
        $path = $image->store('images', 'public');
        return $path;
    }

    // ... other methods ...
}
