<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateHairStylistRequest; // Import the new form request validation class
use App\Http\Requests\UpdateHairStylistRequest; // Import the update form request validation class
use App\Models\User;
use App\Models\Request as HairStylistRequest; // Renamed to avoid confusion with HTTP Request
use App\Models\RequestArea;
use App\Models\RequestMenu;
use App\Models\RequestImage;
use App\Models\Area;
use App\Models\Menu;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    // ... other methods ...

    // Existing methods remain unchanged

    // Method to create a hair stylist request
    public function createHairStylistRequest(HttpRequest $request): JsonResponse
    {
        // Authenticate the user
        if (!Auth::check()) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        // Validate the incoming request
        $validator = Validator::make($request->all(), [
            'area' => 'required|array|min:1',
            'menu' => 'required|array|min:1',
            'hair_concerns' => 'required|max:3000',
            'images' => 'required|array|min:1',
            'images.*' => 'image|mimes:png,jpg,jpeg|max:5120',
        ], [
            'area.required' => 'Area selection is required.',
            'menu.required' => 'Menu selection is required.',
            'hair_concerns.max' => 'Hair concerns and requests must be less than 3000 characters.',
            'images.required' => 'At least one image is required.',
            'images.*.image' => 'Invalid image format or size.',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed.', 'errors' => $validator->errors()], 422);
        }

        // Create the hair stylist request
        $hairStylistRequest = HairStylistRequest::create([
            'user_id' => Auth::id(),
            'hair_concerns' => $request->input('hair_concerns'),
            'status' => 'pending',
        ]);

        // Handle area and menu selections
        $areaNames = [];
        foreach ($request->input('area') as $areaId) {
            $area = Area::find($areaId);
            if (!$area) {
                continue; // Skip if area does not exist
            }
            RequestArea::create([
                'request_id' => $hairStylistRequest->id,
                'area_id' => $areaId,
            ]);
            $areaNames[] = $area->name;
        }

        $menuNames = [];
        foreach ($request->input('menu') as $menuId) {
            $menu = Menu::find($menuId);
            if (!$menu) {
                continue; // Skip if menu does not exist
            }
            RequestMenu::create([
                'request_id' => $hairStylistRequest->id,
                'menu_id' => $menuId,
            ]);
            $menuNames[] = $menu->name;
        }

        // Handle images
        $imagePaths = [];
        foreach ($request->file('images') as $image) {
            // Save the image and create a RequestImage entry
            $path = $image->store('request_images', 'public');
            $imagePaths[] = $path;
            RequestImage::create([
                'request_id' => $hairStylistRequest->id,
                'image_path' => $path,
            ]);
        }

        // Prepare and return the response
        return response()->json([
            'status' => 201,
            'request' => [
                'id' => $hairStylistRequest->id,
                'area' => $areaNames,
                'menu' => $menuNames,
                'hair_concerns' => $hairStylistRequest->hair_concerns,
                'status' => $hairStylistRequest->status,
                'created_at' => $hairStylistRequest->created_at->toIso8601String(),
                'user_id' => $hairStylistRequest->user_id
            ],
            'message' => 'Hair stylist request has been successfully created.'
        ], 201);
    }

    // Method to create or update a hair stylist request
    public function createOrUpdateHairStylistRequest(HttpRequest $request): JsonResponse
    {
        // Authenticate the user based on the "user_id"
        $userId = $request->input('user_id');
        $user = User::find($userId);
        if (!$user || $userId != Auth::id()) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        // Use the CreateHairStylistRequest form request class if it's a POST request
        if ($request->isMethod('post')) {
            $validatedData = (new CreateHairStylistRequest())->validateResolved();
        } else {
            // Validate "area" and "menu" selections are not empty
            $validator = Validator::make($request->all(), [
                'area' => 'required|array|min:1',
                'menu' => 'required|array|min:1',
                'hair_concerns' => 'required|max:3000',
                'images' => 'required|array|min:1',
                'images.*' => 'image|mimes:png,jpg,jpeg|max:5120',
            ]);

            if ($validator->fails()) {
                return response()->json(['message' => 'Validation failed.', 'errors' => $validator->errors()], 422);
            }

            $validatedData = $validator->validated();
        }

        // Check if the authenticated user has an existing valid request
        $existingRequest = HairStylistRequest::where('user_id', $user->id)
                                             ->where('status', 'pending')
                                             ->first();

        if ($existingRequest) {
            // Update the existing request with the new data
            $existingRequest->update([
                'hair_concerns' => $validatedData['hair_concerns'],
            ]);
            $hairStylistRequest = $existingRequest;
        } else {
            // Create a new HairStylistRequest model instance
            $hairStylistRequest = HairStylistRequest::create([
                'user_id' => $user->id,
                'hair_concerns' => $validatedData['hair_concerns'],
                'status' => 'pending', // Set the initial status to 'pending'
            ]);
        }

        // Delete old images if updating an existing request
        if ($existingRequest) {
            $existingRequest->requestImages()->delete();
        }

        // Iterate over the "images" array and create RequestImage instances
        foreach ($validatedData['images'] as $image) {
            // Save the image and create a RequestImage entry
            $path = $image->store('request_images', 'public');
            $requestImage = new RequestImage([
                'request_id' => $hairStylistRequest->id,
                'image_path' => $path,
            ]);
            $requestImage->save();
        }

        // Prepare the response data
        $responseData = [
            'request_id' => $hairStylistRequest->id,
            'status' => $hairStylistRequest->status,
            'message' => 'Hair stylist request registration has been successfully processed.'
        ];

        // Return the response with the newly created or updated request details
        return response()->json($responseData);
    }

    // ... other methods ...

    // Method to cancel a hair stylist request
    public function cancelHairStylistRequest(HttpRequest $request): JsonResponse
    {
        // Validate the request_id and check if it exists in the requests table
        $validatedData = $request->validate([
            'request_id' => 'required|exists:requests,id',
        ]);

        // Retrieve the HairStylistRequest model instance using the request_id
        $hairStylistRequest = HairStylistRequest::findOrFail($validatedData['request_id']);

        // Check if the authenticated user is the owner of the request
        if ($hairStylistRequest->user_id != Auth::id()) {
            return response()->json(['message' => 'Unauthorized access.'], 403);
        }

        // Update the status column of the HairStylistRequest instance to 'canceled'
        $hairStylistRequest->status = 'canceled';

        // Save the changes to the HairStylistRequest instance
        $hairStylistRequest->save();

        // Return a JsonResponse with the request_id, updated status, and a confirmation message
        return response()->json([
            'request_id' => $hairStylistRequest->id,
            'status' => $hairStylistRequest->status,
            'message' => 'Hair stylist request registration has been successfully canceled.'
        ]);
    }

    // ... other methods ...
}
