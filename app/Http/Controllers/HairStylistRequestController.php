<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateHairStylistRequest;
use App\Services\HairStylistRequestService;
use App\Http\Resources\HairStylistRequestResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class HairStylistRequestController extends Controller
{
    protected $hairStylistRequestService;

    public function __construct(HairStylistRequestService $hairStylistRequestService)
    {
        $this->hairStylistRequestService = $hairStylistRequestService;
        $this->middleware('auth'); // Ensure user is authenticated
    }

    public function createHairStylistRequest(CreateHairStylistRequest $request): JsonResponse
    {
        // Ensure the user is authenticated
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Check if the user has permission to create a hair stylist request
        // This is a placeholder for actual permission checking logic
        // if (!Auth::user()->can('create_hair_stylist_request')) {
        //     return response()->json(['error' => 'Forbidden'], 403);
        // }

        $validatedData = $request->validated();
        $hairStylistRequest = $this->hairStylistRequestService->createRequest($validatedData);

        return response()->json(new HairStylistRequestResource($hairStylistRequest), 201);
    }

    public function createOrUpdateHairStylistRequest(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'details' => 'required|string',
            'status' => [
                'sometimes',
                'string',
                Rule::in(['pending', 'approved', 'rejected']) // Assuming these are the predefined status values
            ],
            'user_id' => 'required|exists:users,id',
            'request_image_id' => 'sometimes|nullable|exists:request_images,id'
        ], [
            'details.required' => 'Details are required.',
            'user_id.exists' => 'User not found.',
            'request_image_id.exists' => 'Request image not found.',
            'status.in' => 'Invalid status value.'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $validatedData = $validator->validated();
        if (isset($validatedData['request_id'])) {
            $hairStylistRequest = $this->hairStylistRequestService->updateRequest($validatedData, $validatedData['request_id']);
            $status = 200;
        } else {
            $hairStylistRequest = $this->hairStylistRequestService->createRequest($validatedData);
            $status = 201;
        }

        return response()->json(new HairStylistRequestResource($hairStylistRequest), $status);
    }

    // ... other methods ...
}
