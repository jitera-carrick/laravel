<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Requests\CreateHairStylistRequest;
use App\Http\Requests\UpdateHairStylistRequest;
use App\Models\User;
use App\Models\Request as HairStylistRequest;
use App\Models\RequestArea;
use App\Models\RequestMenu;
use App\Models\RequestImage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UserController extends Controller
{
    // ... other methods ...

    // Existing methods remain unchanged

    // Method to create or update a hair stylist request
    public function createOrUpdateHairStylistRequest(HttpRequest $request): JsonResponse
    {
        // ... existing code for createOrUpdateHairStylistRequest method ...
    }

    // Method to cancel a hair stylist request
    public function cancelHairStylistRequest(HttpRequest $request): JsonResponse
    {
        // ... existing code for cancelHairStylistRequest method ...
    }

    // Method to update a hair stylist request
    public function updateHairStylistRequest(HttpRequest $request, $id): JsonResponse
    {
        // ... existing code for updateHairStylistRequest method ...
    }

    /**
     * Delete an image from a hair stylist request.
     *
     * @param  HttpRequest $request
     * @param  int  $request_id
     * @param  int  $image_id
     * @return JsonResponse
     */
    public function deleteRequestImage(HttpRequest $request, int $request_id, int $image_id): JsonResponse
    {
        $user = Auth::user();
        $hairStylistRequest = HairStylistRequest::where('id', $request_id)->where('user_id', $user->id)->first();

        if (!$hairStylistRequest) {
            return response()->json(['message' => 'Request not found or not authorized to delete image from this request.'], 403);
        }

        $image = RequestImage::where('id', $image_id)->where('request_id', $request_id)->first();

        if (!$image) {
            return response()->json(['message' => 'Image not found or does not belong to the specified request.'], 404);
        }

        // Delete the image file from storage
        Storage::delete($image->image_path);

        // Delete the image record from the database
        $image->delete();

        return response()->json(['status' => 200, 'message' => 'Image has been successfully deleted from your request.']);
    }

    // ... other methods ...
}
