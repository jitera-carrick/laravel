<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request as HttpRequest;
use App\Http\Requests\UpdateHairStylistRequest;
use App\Http\Requests\CreateHairStylistRequest;
use App\Http\Requests\DeleteImageRequest;
use App\Models\Request;
use App\Models\RequestAreaSelection;
use App\Models\RequestMenuSelection;
use App\Models\RequestImage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\SuccessResource;
use App\Models\StylistRequest;

class RequestController extends Controller
{
    // ... other methods ...

    // Method to create a hair stylist request
    public function createHairStylistRequest(CreateHairStylistRequest $httpRequest): JsonResponse
    {
        $user = Auth::user();

        if ($httpRequest->user_id != $user->id) {
            return response()->json(['message' => 'Unauthorized user.'], 401);
        }

        // Create the request
        $hairRequest = new Request([
            'user_id' => $httpRequest->user_id,
            'hair_concerns' => $httpRequest->hair_concerns,
            'status' => 'pending',
        ]);
        $hairRequest->save();

        // Create area selections
        foreach ($httpRequest->area_id as $areaId) {
            $hairRequest->areas()->attach($areaId);
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
                'image_path' => $imagePath
            ]);
        }

        return response()->json([
            'status' => 201,
            'request' => [
                'id' => $hairRequest->id,
                'user_id' => $hairRequest->user_id,
                'area' => $hairRequest->areas()->get(),
                'menu' => $hairRequest->menus()->get(),
                'hair_concerns' => $hairRequest->hair_concerns,
                'status' => $hairRequest->status,
                'created_at' => $hairRequest->created_at->toIso8601String(),
                'updated_at' => $hairRequest->updated_at->toIso8601String(),
            ]
        ], 201);
    }

    // Method to update a hair stylist request
    public function updateHairStylistRequest(UpdateHairStylistRequest $request, $id): JsonResponse
    {
        // ... existing update method code ...
    }

    // Method to delete an image from a hair stylist request
    public function deleteImage(DeleteImageRequest $httpRequest, $request_id, $image_id): JsonResponse
    {
        $user = Auth::user();
        $request = Request::where('id', $request_id)->where('user_id', $user->id)->first();

        if (!$request) {
            return response()->json(['message' => 'Request not found.'], 404);
        }

        $requestImage = RequestImage::where('id', $image_id)->where('request_id', $request_id)->first();

        if (!$requestImage) {
            return response()->json(['message' => 'Image not found or does not belong to the specified request.'], 404);
        }

        // Check if the user is the owner of the request or an admin
        if ($user->id !== $request->user_id && !$user->can('admin')) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        try {
            $requestImage->delete();
            return response()->json([
                'status' => 200,
                'message' => 'Image has been successfully deleted.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Failed to delete the image.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // ... other methods ...
}
