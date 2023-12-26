<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StylistRequest;
use App\Models\Image;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class StylistRequestController extends Controller
{
    // Existing methods in the controller...

    public function createStylistRequest(Request $request)
    {
        // Start transaction
        DB::beginTransaction();

        try {
            // Validate the input data
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer|exists:users,id',
                // 'details' => 'required|string', // This field is not required by the requirement, so it should be removed.
                'area' => 'required|string',
                'gender' => 'required|in:male,female,other',
                'birth_date' => 'required|date',
                'display_name' => 'required|string',
                'menu' => 'required|string',
                'hair_concerns' => 'required|string',
                'images' => 'sometimes|array',
                'images.*' => 'image|distinct|min:3|max:5120' // Validate image format and size (5MB max)
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            // Create a new stylist request
            $stylistRequest = new StylistRequest([
                'user_id' => $request->input('user_id'),
                // 'details' => $request->input('details'), // This field is not required by the requirement, it should be removed.
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
                foreach ($request->file('images') as $imageFile) {
                    // Validate image format and size here if needed
                    $imageValidator = Validator::make(['image' => $imageFile], [
                        'image' => 'image|distinct|min:3|max:5120' // Validate image format and size (5MB max)
                    ]);

                    if ($imageValidator->fails()) {
                        throw new \Exception("Invalid image format or size");
                    }

                    $filePath = Storage::put('stylist_requests', $imageFile); // Store the image and get the path
                    $image = new Image([
                        'file_path' => $filePath,
                        'stylist_request_id' => $stylistRequest->id
                    ]);
                    $image->save();
                    $imagePaths[] = $filePath;
                }
            }

            // Commit transaction
            DB::commit();

            // Return a JSON response with the created request details
            return response()->json([
                'stylist_request_id' => $stylistRequest->id // Return only the stylist request ID as per requirement
            ], 201);
        } catch (\Exception $e) {
            // Rollback transaction on error
            DB::rollBack();

            // Handle specific exceptions or return a generic error message
            if ($e instanceof ModelNotFoundException) {
                return response()->json(['error' => 'The user does not exist'], 404);
            }

            return response()->json(['error' => 'An error occurred while creating the stylist request: ' . $e->getMessage()], 500);
        }
    }
}
