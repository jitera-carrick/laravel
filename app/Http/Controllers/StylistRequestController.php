<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StylistRequest;
use App\Models\Image;
use Illuminate\Support\Facades\Validator;

class StylistRequestController extends Controller
{
    // Existing methods in the controller...

    public function createStylistRequest(Request $request)
    {
        // Validate the input data
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer|exists:users,id',
            'details' => 'required|string',
            'area' => 'required|string',
            'gender' => 'required|in:male,female,other',
            'birth_date' => 'required|date',
            'display_name' => 'required|string',
            'menu' => 'required|string',
            'hair_concerns' => 'required|string',
            'images' => 'sometimes|array',
            'images.*' => 'string' // Assuming the file paths are provided as strings
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Create a new stylist request
        $stylistRequest = new StylistRequest([
            'user_id' => $request->input('user_id'),
            'details' => $request->input('details'),
            'area' => $request->input('area'),
            'gender' => $request->input('gender'),
            'birth_date' => $request->input('birth_date'),
            'display_name' => $request->input('display_name'),
            'menu' => $request->input('menu'),
            'hair_concerns' => $request->input('hair_concerns'),
            'status' => 'pending', // Set the default status to 'pending'
        ]);
        $stylistRequest->save();

        // If images are provided, create entries for each image
        $imagePaths = [];
        if ($request->has('images')) {
            foreach ($request->input('images') as $filePath) {
                $image = new Image([
                    'file_path' => $filePath,
                    'stylist_request_id' => $stylistRequest->id
                ]);
                $image->save();
                $imagePaths[] = $filePath;
            }
        }

        // Return a JSON response with the created request details
        return response()->json([
            'message' => 'Stylist request created successfully.',
            'stylist_request' => [
                'id' => $stylistRequest->id,
                'user_id' => $stylistRequest->user_id,
                'details' => $stylistRequest->details,
                'area' => $stylistRequest->area,
                'gender' => $stylistRequest->gender,
                'birth_date' => $stylistRequest->birth_date,
                'display_name' => $stylistRequest->display_name,
                'menu' => $stylistRequest->menu,
                'hair_concerns' => $stylistRequest->hair_concerns,
                'status' => $stylistRequest->status,
                'images' => $imagePaths
            ]
        ], 201);
    }
}
