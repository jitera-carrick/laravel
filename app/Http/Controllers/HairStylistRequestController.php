<?php

namespace App\Http\Controllers;

use App\Models\Request;
use App\Models\RequestAreaSelection;
use App\Models\RequestMenuSelection;
use App\Models\RequestImage;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class HairStylistRequestController extends Controller
{
    public function createHairStylistRequest(HttpRequest $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'area' => 'required|array|min:1',
            'area.*' => 'required|exists:areas,id',
            'menu' => 'required|array|min:1',
            'menu.*' => 'required|exists:menus,id',
            'images' => 'required|array|min:1',
            'images.*' => 'required|image|mimes:png,jpg,jpeg|max:5120', // 5MB = 5120KB
            'hair_concerns' => 'required|string|max:3000',
        ], [
            'area.required' => 'Area selection is required.',
            'menu.required' => 'Menu selection is required.',
            'hair_concerns.max' => 'Hair concerns and requests must be less than 3000 characters.',
            'images.*.image' => 'Invalid image format or size.',
            'images.*.mimes' => 'Invalid image format or size.',
            'images.*.max' => 'Invalid image format or size.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'message' => 'The given data was invalid.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $validatedData = $validator->validated();

        try {
            DB::beginTransaction();

            $hairStylistRequest = new Request();
            $hairStylistRequest->user_id = $validatedData['user_id'];
            $hairStylistRequest->hair_concerns = $validatedData['hair_concerns'];
            $hairStylistRequest->status = 'pending'; // Assuming 'pending' is a valid status
            $hairStylistRequest->save();

            foreach ($validatedData['area'] as $areaId) {
                $requestAreaSelection = new RequestAreaSelection();
                $requestAreaSelection->request_id = $hairStylistRequest->id;
                $requestAreaSelection->area_id = $areaId;
                $requestAreaSelection->save();
            }

            foreach ($validatedData['menu'] as $menuId) {
                $requestMenuSelection = new RequestMenuSelection();
                $requestMenuSelection->request_id = $hairStylistRequest->id;
                $requestMenuSelection->menu_id = $menuId;
                $requestMenuSelection->save();
            }

            foreach ($validatedData['images'] as $image) {
                $path = $image->store('request_images', 'public'); // Assuming 'public' disk is configured
                $requestImage = new RequestImage();
                $requestImage->request_id = $hairStylistRequest->id;
                $requestImage->image_path = $path;
                $requestImage->save();
            }

            DB::commit();

            return response()->json([
                'status' => 201,
                'message' => 'Hair stylist request created successfully.',
                'data' => $hairStylistRequest->load('requestAreaSelections', 'requestMenuSelections', 'requestImages'),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 500,
                'message' => 'An error occurred while creating the hair stylist request.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
