<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Request as RequestModel;
use App\Models\RequestImage;
use Illuminate\Support\Facades\Auth;

class RequestController extends Controller
{
    public function createHairStylistRequest(Request $httpRequest)
    {
        $validatedData = $httpRequest->validate([
            'user_id' => 'required|exists:users,id', // Ensure the user_id exists in the users table
            'area' => 'required',
            'menu' => 'required',
            'hair_concerns' => 'required|max:3000',
            'image_paths' => 'required|array|max:3',
            'image_paths.*' => 'file|mimes:png,jpg,jpeg|max:5120', // 5MB
        ]);

        // Check if the authenticated user matches the provided user_id
        if (Auth::id() !== (int) $validatedData['user_id']) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Create the request
        $request = new RequestModel([
            'area' => $validatedData['area'],
            'menu' => $validatedData['menu'],
            'hair_concerns' => $validatedData['hair_concerns'],
            'user_id' => Auth::id(),
            'status' => 'available', // Assuming 'available' is a valid status
        ]);
        $request->save();

        // Save the images
        foreach ($validatedData['image_paths'] as $imagePath) {
            $requestImage = new RequestImage([
                'image_path' => $imagePath,
                'request_id' => $request->id,
            ]);
            $requestImage->save();
        }

        // Return the response
        return response()->json([
            'request_id' => $request->id,
            'status' => $request->status,
            'message' => 'Hair stylist request registered successfully.',
        ]);
    }
}
