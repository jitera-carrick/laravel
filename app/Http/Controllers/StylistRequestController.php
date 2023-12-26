<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StylistRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class StylistRequestController extends Controller
{
    // Existing methods in the controller...

    public function store(Request $request)
    {
        // Start transaction
        DB::beginTransaction();

        try {
            // Validate the input data
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer|exists:users,id',
                'area' => 'required|string',
                'gender' => 'required|in:male,female,do_not_answer',
                'birth_date' => 'required|date',
                'display_name' => 'required|string|max:20',
                'menu' => 'required|string',
                'hair_concerns' => 'nullable|string|max:2000',
                'images' => 'sometimes|array|max:3',
                'images.*' => 'nullable|file|image|mimes:png,jpg,jpeg|max:5120',
            ], [
                'area.required' => 'Area selection is required.',
                'gender.required' => 'Gender selection is required.',
                'gender.in' => 'Gender selection is invalid.',
                'birth_date.required' => 'A valid birth date is required.',
                'display_name.required' => 'Display name is required.',
                'display_name.max' => 'Display name cannot exceed 20 characters.',
                'menu.required' => 'Menu selection is required.',
                'hair_concerns.max' => 'Hair concerns cannot exceed 2000 characters.',
                'images.max' => 'No more than three images can be uploaded.',
                'images.*.mimes' => 'Invalid image format.',
                'images.*.max' => 'Image size must be under 5MB.',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 400);
            }

            // Create a new stylist request
            $stylistRequest = StylistRequest::create($validator->validated() + ['status' => 'pending']);

            // If images are provided, create entries for each image
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $imageFile) {
                    $path = $imageFile->store('stylist_requests', 'public');
                    $stylistRequest->images()->create(['file_path' => $path]);
                }
            }

            // Commit transaction
            DB::commit();

            // Return a JSON response with the created request details
            return response()->json([
                'status' => 201,
                'stylist_request' => [
                    'id' => $stylistRequest->id,
                    'area' => $stylistRequest->area,
                    'gender' => $stylistRequest->gender,
                    'birth_date' => $stylistRequest->birth_date,
                    'display_name' => $stylistRequest->display_name,
                    'menu' => $stylistRequest->menu,
                    'hair_concerns' => $stylistRequest->hair_concerns,
                    'images' => $stylistRequest->images->map(function ($image) {
                        return [
                            'file_path' => $image->file_path,
                            'created_at' => $image->created_at->toIso8601String()
                        ];
                    }),
                    'created_at' => $stylistRequest->created_at->toIso8601String(),
                ]
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

    // Other existing methods...
}
