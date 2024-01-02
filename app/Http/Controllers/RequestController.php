<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateHairStylistRequest;
use App\Models\Request;
use App\Models\RequestAreaSelection;
use App\Models\RequestMenuSelection;
use App\Models\RequestImage;
use App\Models\Area;
use App\Models\Menu;
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
            'area_ids' => 'required|array|min:1|exists:areas,id',
            'menu_ids' => 'required|array|min:1|exists:menus,id',
            'hair_concerns' => 'required|string|max:3000',
            'image_paths' => 'required|array|max:3',
            'image_paths.*' => 'string', // Additional image format and file size validation can be added here
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        // Update area selections
        RequestAreaSelection::where('request_id', $id)->delete();
        foreach ($request->area_ids as $areaId) {
            RequestAreaSelection::create([
                'request_id' => $id,
                'area_id' => $areaId,
            ]);
        }

        // Update menu selections
        RequestMenuSelection::where('request_id', $id)->delete();
        foreach ($request->menu_ids as $menuId) {
            RequestMenuSelection::create([
                'request_id' => $id,
                'menu_id' => $menuId,
            ]);
        }

        // Update hair concerns
        $hairRequest->update(['hair_concerns' => $request->hair_concerns]);

        // Update images
        RequestImage::where('request_id', $id)->delete();
        foreach ($request->image_paths as $imagePath) {
            // Validate the image format and size before storing
            $image = Storage::disk('public')->get($imagePath); // Assuming the image already exists in storage
            $imageSize = Storage::disk('public')->size($imagePath);
            $imageExtension = strtolower(pathinfo($imagePath, PATHINFO_EXTENSION));
            if (!in_array($imageExtension, ['png', 'jpg', 'jpeg']) || $imageSize > 5120 * 1024) {
                return response()->json(['message' => 'Invalid image format or file size.'], 422);
            }

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

    // ... other methods ...
}
