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
use Illuminate\Support\Facades\Log;

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

    // Method to check and delete past requests
    public function checkAndDeletePastRequest(int $user_id): bool
    {
        try {
            $authenticatedUser = Auth::user();

            if ($authenticatedUser->id !== $user_id) {
                Log::warning("User ID does not match the authenticated user's ID.", ['user_id' => $user_id]);
                return false;
            }

            $existingRequest = Request::where('user_id', $user_id)->first();

            if (!$existingRequest) {
                return false;
            }

            // Assuming there is a relationship method `confirmedTreatmentPlan` in the `Request` model
            $confirmedTreatmentPlan = $existingRequest->confirmedTreatmentPlan()->where('date', '<', now())->first();

            if ($confirmedTreatmentPlan) {
                $existingRequest->delete();
                return true;
            }

            return false;
        } catch (\Exception $e) {
            Log::error("Error in checkAndDeletePastRequest: " . $e->getMessage(), [
                'user_id' => $user_id,
                'exception' => $e,
            ]);
            return false;
        }
    }

    // ... other methods ...
}
