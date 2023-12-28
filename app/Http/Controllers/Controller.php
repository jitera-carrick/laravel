<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use App\Models\Request as RequestModel;
use App\Models\RequestImage;
use Illuminate\Support\Facades\Auth;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    public function createHairStylistRequest(Request $request)
    {
        // Requirement 1: Validate that the "user_id" corresponds to a logged-in customer.
        if (!Auth::check()) {
            return response()->json(['error' => 'User must be logged in to create a request.'], 401);
        }

        // Requirement 4, 5, 6: Validate the "area", "menu", "hair_concerns", and "image_paths".
        $validatedData = $request->validate([
            'area' => 'required',
            'menu' => 'required',
            'hair_concerns' => 'string|max:3000',
            'image_paths' => 'array|max:3',
            'image_paths.*' => 'file|mimes:png,jpg,jpeg|max:5120', // 5MB
        ]);

        // Requirement 8: Create a new entry in the "requests" table.
        $hairStylistRequest = RequestModel::create([
            'area' => $request->input('area'),
            'menu' => $request->input('menu'),
            'hair_concerns' => $request->input('hair_concerns'),
            'status' => 'available', // Requirement 10: Update the "status" of the request.
            'user_id' => Auth::id(), // Requirement 1: Use the logged-in user's ID.
        ]);

        // Requirement 9: For each "image_path", create a new entry in the "request_images" table.
        if ($request->hasfile('image_paths')) {
            foreach ($request->file('image_paths') as $file) {
                $path = $file->store('public/request_images');
                RequestImage::create([
                    'image_path' => $path,
                    'request_id' => $hairStylistRequest->id,
                ]);
            }
        }

        // Requirement 11: Return a success message.
        return response()->json([
            'request_id' => $hairStylistRequest->id,
            'status' => $hairStylistRequest->status,
            'message' => 'Hair stylist request registered successfully.'
        ], 201);
    }
}
