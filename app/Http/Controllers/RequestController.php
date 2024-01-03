<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateHairStylistRequest;
use App\Models\Request;
use App\Models\RequestAreaSelection;
use App\Models\RequestMenuSelection;
use App\Models\RequestImage;
use App\Models\StylistRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request as HttpRequest;

class RequestController extends Controller
{
    // ... other methods ...

    // Method to create a new request
    public function create(HttpRequest $httpRequest): JsonResponse
    {
        $validator = Validator::make($httpRequest->all(), [
            'area' => 'required|string|max:255',
            'gender' => 'required|in:Male,Female,Do Not Answer',
            'date_of_birth' => 'required|date',
            'display_name' => 'required|string|max:20',
            'menu' => 'required|string|max:255',
            'hair_concerns' => 'required|string|max:2000',
            'images' => 'sometimes|array|max:3',
            'images.*' => 'image|mimes:png,jpg,jpeg|max:5120',
        ], [
            'area.required' => 'Area selection is required.',
            'gender.required' => 'Gender selection is required.',
            'gender.in' => 'Invalid gender selection.',
            'date_of_birth.required' => 'Birth date is required.',
            'display_name.required' => 'Display name is required.',
            'display_name.max' => 'Display name cannot exceed 20 characters.',
            'menu.required' => 'Menu selection is required.',
            'hair_concerns.required' => 'Hair concerns is required.',
            'hair_concerns.max' => 'Hair concerns cannot exceed 2000 characters.',
            'images.*.image' => 'Invalid image format or size.',
            'images.*.mimes' => 'Invalid image format or size.',
            'images.*.max' => 'Invalid image format or size.',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        DB::beginTransaction();
        try {
            $user = Auth::user();
            $request = new Request();
            $request->user_id = $user->id;
            $request->area = $httpRequest->area;
            $request->gender = $httpRequest->gender;
            $request->date_of_birth = $httpRequest->date_of_birth;
            $request->display_name = $httpRequest->display_name;
            $request->menu = $httpRequest->menu;
            $request->hair_concerns = $httpRequest->hair_concerns;
            $request->status = 'pending'; // default status
            $request->save();

            // Handle images if provided
            if ($httpRequest->has('images')) {
                foreach ($httpRequest->images as $image) {
                    $path = $image->store('request_images', 'public');
                    $requestImage = new RequestImage();
                    $requestImage->request_id = $request->id;
                    $requestImage->image_path = $path;
                    $requestImage->save();
                }
            }

            // Update the StylistRequest status if needed
            // Assuming there is a method in the StylistRequest model to update the status
            StylistRequest::updateStatusForRequest($request->id, 'new_status');

            DB::commit();

            return response()->json([
                'status' => 201,
                'request' => $request->fresh(),
                'message' => 'Request created successfully.',
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to create the request.'], 500);
        }
    }

    // ... other methods ...
}
