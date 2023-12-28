<?php

namespace App\Http\Controllers;

use App\Models\Request as HairStylistRequest; // Renamed to avoid confusion with HTTP Request
use App\Models\RequestImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;

class HairStylistRequestController extends Controller
{
    // ... other methods ...

    // New method to create a hair stylist request
    public function createHairStylistRequest(Request $request): JsonResponse
    {
        // Check if the user is authenticated
        if (!Auth::check()) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        // Validate the incoming request data
        $validator = Validator::make($request->all(), [
            'area' => 'required|string|max:255',
            'menu' => 'required|string|max:255',
            'hair_concerns' => 'sometimes|string|max:3000',
            'images' => 'sometimes|array|max:3',
            'images.*' => 'image|mimes:jpeg,png,jpg|max:5120', // 5MB = 5120KB
        ], [
            'area.required' => 'Please select an area.',
            'menu.required' => 'Please select a menu.',
            'hair_concerns.max' => 'Hair concerns and requests must be less than 3000 characters.',
            'images.max' => 'You can only upload up to 3 images.',
            'images.*.mimes' => 'Invalid image format or file size too large.',
            'images.*.max' => 'Invalid image format or file size too large.',
        ]);

        // If validation fails, return a response with a 422 Unprocessable Entity status code and the validation errors
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // If validation passes, create a new Request model instance and fill it with the validated data
        $hairStylistRequest = new HairStylistRequest($validator->validated());
        $hairStylistRequest->user_id = Auth::id(); // Set the user_id to the authenticated user's id
        $hairStylistRequest->status = 'pending'; // Set the default status
        $hairStylistRequest->save(); // Save the model to the database

        // If 'images' are provided, iterate over them and create RequestImage model instances for each image
        if ($request->has('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('request_images', 'public'); // Store the image and get the path
                $requestImage = new RequestImage(['image_path' => $path]);
                $hairStylistRequest->requestImages()->save($requestImage); // Associate the image with the created Request model
            }
        }

        // Return a JSON response with a 201 Created status code and the created request data
        return response()->json($hairStylistRequest, 201);
    }

    // ... other methods ...
}
