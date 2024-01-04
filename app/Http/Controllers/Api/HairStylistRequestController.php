<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateHairStylistRequest;
use App\Http\Resources\HairStylistRequestResource;
use App\Models\Request;
use App\Models\User;
use App\Models\StylistRequest;
use App\Models\RequestImage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request as HttpRequest;

class HairStylistRequestController extends Controller
{
    // Existing methods...

    public function store(CreateHairStylistRequest $request)
    {
        // This method is now redundant and should be replaced by createHairStylistRequest.
        // Keeping it for backward compatibility or in case it's used elsewhere.
        // Check if the user exists
        $user = User::find($request->user_id);
        if (!$user) {
            throw new ModelNotFoundException('User not found.');
        }

        // Create a new Request instance with the validated data
        $hairStylistRequest = new Request([
            'area' => $request->area,
            'menu' => $request->menu,
            'hair_concerns' => $request->hair_concerns,
            'user_id' => $request->user_id,
            'status' => 'pending', // default status
        ]);

        // Save the new Request instance to the database
        $hairStylistRequest->save();

        // Return a response with the newly created request
        return new HairStylistRequestResource($hairStylistRequest);
    }

    // New method to handle the POST request for creating a hair stylist request
    public function createHairStylistRequest(HttpRequest $httpRequest): JsonResponse
    {
        // Validate the request data
        $validator = Validator::make($httpRequest->all(), [
            'area' => 'required|string',
            'menu' => 'required|string',
            'hair_concerns' => 'required|string',
            'images' => 'required|array',
            'images.*' => 'file|image',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $validatedData = $validator->validated();

        // Check if the user is authenticated
        if (!Auth::check()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Create a new StylistRequest instance and fill it with validated data
        $stylistRequest = new StylistRequest([
            'area' => $validatedData['area'],
            'menu' => $validatedData['menu'],
            'hair_concerns' => $validatedData['hair_concerns'],
            'user_id' => Auth::id(),
            'status' => 'pending', // default status
        ]);

        // Save the new StylistRequest instance to the database
        $stylistRequest->save();

        // Handle file uploads and associate them with the request
        foreach ($validatedData['images'] as $image) {
            $requestImage = new RequestImage();
            $requestImage->path = $image->store('images'); // Assuming 'images' is a valid disk in filesystems.php
            $requestImage->stylist_request_id = $stylistRequest->id;
            $requestImage->save();
        }

        // Return a response with the newly created request
        return response()->json([
            'status' => 'success',
            'data' => new HairStylistRequestResource($stylistRequest)
        ], 201);
    }

    // Other methods...
}
