<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request as HttpRequest;
use App\Http\Requests\UpdateHairStylistRequest;
use App\Http\Requests\DeleteImageRequest; // Added line
use App\Models\Request;
use App\Models\RequestAreaSelection;
use App\Models\RequestMenuSelection;
use App\Models\RequestImage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\SuccessResource; // Assuming SuccessResource exists and is imported correctly
use App\Models\StylistRequest; // Added import for StylistRequest

class RequestController extends Controller
{
    // ... other methods ...
    
    // Method to create a hair stylist request
    public function createHairStylistRequest(HttpRequest $httpRequest): JsonResponse
    {
        // ... same as in the new code ...
    }

    // Method to update a hair stylist request
    public function updateHairStylistRequest(UpdateHairStylistRequest $request, $id): JsonResponse
    {
        // The new code introduces a new method signature with a different parameter ($stylistRequest)
        // We need to combine the logic of the existing update method with the new updateHairStylistRequest method.
        // Since the existing code does not use type-hinted StylistRequest, we will adapt the new method to work with the existing code.
        
        $hairRequest = Request::find($id);
        $user = Auth::user();

        if (!$hairRequest || $hairRequest->user_id !== $user->id) {
            return response()->json(['message' => 'Request not found or unauthorized.'], 404);
        }

        try {
            $updatedFields = $request->validated();

            // Update area and menu selections within a transaction
            DB::transaction(function () use ($request, $id, $hairRequest, $updatedFields) {
                // Update area selections
                RequestAreaSelection::where('request_id', $id)->delete();
                foreach ($updatedFields['area_id'] as $areaId) {
                    RequestAreaSelection::create([
                        'request_id' => $id,
                        'area_id' => $areaId,
                    ]);
                }

                // Update menu selections
                RequestMenuSelection::where('request_id', $id)->delete();
                foreach ($updatedFields['menu_id'] as $menuId) {
                    RequestMenuSelection::create([
                        'request_id' => $id,
                        'menu_id' => $menuId,
                    ]);
                }

                // Update hair concerns
                $hairRequest->update(['hair_concerns' => $updatedFields['hair_concerns']]);

                // Update images if they exist in the request
                if (isset($updatedFields['image_path'])) {
                    RequestImage::where('request_id', $id)->delete();
                    foreach ($updatedFields['image_path'] as $image) {
                        // Store the image and get the path
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
                'message' => 'Hair stylist request updated successfully',
                'request_id' => $id,
                'request' => $hairRequest->fresh(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Failed to update the hair stylist request.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Method to cancel a hair stylist request
    public function cancelHairStylistRequest(int $id): JsonResponse
    {
        // ... same as in the existing code ...
    }

    // Method to delete an image from a hair stylist request
    public function deleteImage(HttpRequest $httpRequest, $request_id, $image_id): JsonResponse
    {
        // The new code introduces a new method signature with a different parameter ($httpRequest)
        // We need to combine the logic of the existing deleteRequestImage method with the new deleteImage method.
        // Since the existing code uses a custom request (DeleteImageRequest), we will adapt the new method to work with the existing code.
        
        $user = Auth::user();
        $request = Request::where('id', $request_id)->where('user_id', $user->id)->first();

        if (!$request) {
            return response()->json(['message' => 'Request not found or unauthorized.'], 404);
        }

        $requestImage = RequestImage::where('id', $image_id)->where('request_id', $request_id)->first();

        if (!$requestImage) {
            return response()->json(['message' => 'Image not found or does not belong to the request.'], 404);
        }

        try {
            $requestImage->delete();
            return new SuccessResource(['message' => 'Image deleted successfully.']); // Use SuccessResource as in the existing code
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to delete the image.'], 500);
        }
    }

    // ... other methods ...
}
