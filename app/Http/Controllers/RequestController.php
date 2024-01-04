<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request as HttpRequest;
use App\Http\Requests\UpdateHairStylistRequest;
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
    public function createHairStylistRequest(HttpRequest $httpRequest): JsonResponse
    {
        $user = Auth::user();

        // Validate the request
        $validator = Validator::make($httpRequest->all(), [
            'area' => 'required|string',
            'menu' => 'required|string',
            'hair_concerns' => 'required|string|max:3000',
            'images' => 'required|array',
            'images.*' => 'file|image|mimes:png,jpg,jpeg|max:5120', // 5MB
        ], [
            'area.required' => 'Area is required.',
            'menu.required' => 'Menu is required.',
            'hair_concerns.required' => 'Hair concerns are required.',
            'images.required' => 'Images must be an array of files.',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        // Create the request
        $hairRequest = Request::create([
            'user_id' => $user->id,
            'hair_concerns' => $httpRequest->hair_concerns,
            'status' => 'pending', // Assuming 'pending' is a valid status
        ]);

        // Create area selections
        RequestAreaSelection::create([
            'request_id' => $hairRequest->id,
            'area_id' => $httpRequest->area,
        ]);

        // Create menu selections
        RequestMenuSelection::create([
            'request_id' => $hairRequest->id,
            'menu_id' => $httpRequest->menu,
        ]);

        // Create images
        foreach ($httpRequest->images as $image) {
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
                'area' => $httpRequest->area,
                'menu' => $httpRequest->menu,
                'hair_concerns' => $httpRequest->hair_concerns,
                'status' => 'pending',
                'created_at' => $hairRequest->created_at->toIso8601String(),
                'user_id' => $user->id,
            ],
        ], 201);
    }

    // ... existing methods ...
}
