
<?php

namespace App\Http\Controllers;

use App\Services\RequestService;
use Illuminate\Http\Request as HttpRequest;
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
    public function updateHairStylistRequest(UpdateHairStylistRequest $httpRequest, RequestService $requestService, $requestId): JsonResponse
    {
        try {
            $user = Auth::user();
            $hairRequest = $requestService->findByIdAndUserId($requestId, $user->id);

            if (!$hairRequest) {
                return response()->json(['message' => 'Hair stylist request not found or access denied.'], 404);
            }

            $updatedRequest = $requestService->update($hairRequest, $httpRequest->validated());

            return response()->json([
                'status' => 200,
                'request' => [
                    'id' => $updatedRequest->id,
                    'area_id' => $updatedRequest->area_id,
                    'menu_id' => $updatedRequest->menu_id,
                    'hair_concerns' => $updatedRequest->hair_concerns,
                    'status' => $updatedRequest->status,
                    'updated_at' => $updatedRequest->updated_at->toIso8601String(),
                    'user_id' => $user->id,
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred while updating the request.', 'error' => $e->getMessage()], 500);
        }
    }

    // ... existing methods ...
}
