<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateHairStylistRequest;
use App\Models\Request as HairStylistRequest; // Renamed to avoid confusion with HTTP Request
use App\Models\RequestImage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class HairStylistRequestController extends Controller
{
    // ... other methods ...

    // New update method for HairStylistRequest
    public function update(UpdateHairStylistRequest $request, $id): JsonResponse
    {
        // Use route model binding to ensure that the $id provided corresponds to an existing Request
        $hairStylistRequest = HairStylistRequest::find($id);
        if (!$hairStylistRequest) {
            return response()->json(['message' => 'Request not found.'], 404);
        }

        // Check if the authenticated user is the owner of the request
        if ($hairStylistRequest->user_id != Auth::id()) {
            return response()->json(['message' => 'Unauthorized access.'], 403);
        }

        // Update the Request model with the new 'area', 'menu', and 'hair_concerns' values
        $hairStylistRequest->fill($request->validated());
        $hairStylistRequest->save();

        // If 'images' are provided, handle the file upload logic
        if ($request->hasFile('images')) {
            // Delete old images
            $oldImages = $hairStylistRequest->requestImages;
            foreach ($oldImages as $oldImage) {
                Storage::delete($oldImage->image_path);
                $oldImage->delete();
            }

            // Upload new images and update the RequestImage model
            foreach ($request->file('images') as $image) {
                $path = $image->store('request_images', 'public');
                RequestImage::create([
                    'image_path' => $path,
                    'request_id' => $hairStylistRequest->id,
                ]);
            }
        }

        // Return a JSON response with a 200 status code and the updated request data on success
        return response()->json([
            'status' => 200,
            'request' => $hairStylistRequest->load('requestImages')
        ], 200);
    }

    // ... other methods ...
}
