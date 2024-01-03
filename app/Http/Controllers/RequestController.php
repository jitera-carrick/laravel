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
            'area' => 'required|array',
            'area.*' => 'required|string|max:255',
            'gender' => 'required|string|max:255',
            'date_of_birth' => 'required|date',
            'display_name' => 'required|string|max:20',
            'menu' => 'required|array',
            'menu.*' => 'required|string|max:255',
            'hair_concerns' => 'required|string|max:2000',
            'images' => 'sometimes|array|max:3',
            'images.*' => 'image|mimes:png,jpg,jpeg|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        DB::beginTransaction();
        try {
            $user = Auth::user();
            $request = new Request();
            $request->user_id = $user->id;
            $request->area = json_encode($httpRequest->area);
            $request->gender = $httpRequest->gender;
            $request->date_of_birth = $httpRequest->date_of_birth;
            $request->display_name = $httpRequest->display_name;
            $request->menu = json_encode($httpRequest->menu);
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
                'status' => 200,
                'request_id' => $request->id,
                'message' => 'Request created successfully.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to create the request.'], 500);
        }
    }

    // Method to update a hair stylist request
    public function update(UpdateHairStylistRequest $httpRequest, $id): JsonResponse
    {
        $hairRequest = Request::find($id);
        $user = Auth::user();

        if (!$hairRequest || $hairRequest->user_id !== $user->id) {
            return response()->json(['message' => 'Request not found or unauthorized.'], 404);
        }

        // Validate the request
        $validator = Validator::make($httpRequest->all(), [
            'area' => 'required|array|min:1',
            'menu' => 'required|array|min:1',
            'hair_concerns' => 'required|string|max:3000',
            'images' => 'required|array|min:1',
            'images.*' => 'image|mimes:png,jpg,jpeg|max:5120', // 5MB
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        // Update area selections
        RequestAreaSelection::where('request_id', $id)->delete();
        foreach ($httpRequest->area as $areaId) {
            RequestAreaSelection::create([
                'request_id' => $id,
                'area_id' => $areaId,
            ]);
        }

        // Update menu selections
        RequestMenuSelection::where('request_id', $id)->delete();
        foreach ($httpRequest->menu as $menuId) {
            RequestMenuSelection::create([
                'request_id' => $id,
                'menu_id' => $menuId,
            ]);
        }

        // Update hair concerns
        $hairRequest->update(['hair_concerns' => $httpRequest->hair_concerns]);

        // Update images
        RequestImage::where('request_id', $id)->delete();
        foreach ($httpRequest->images as $image) {
            // Store the image and get the path
            $imagePath = Storage::disk('public')->put('request_images', $image);
            RequestImage::create([
                'request_id' => $id,
                'image_path' => $imagePath,
            ]);
        }

        return response()->json([
            'status' => 200,
            'request' => $hairRequest->fresh(),
        ]);
    }

    // ... other methods ...
}
