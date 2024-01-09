<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateHairStylistRequest;
use App\Services\HairStylistRequestService;
use App\Http\Resources\HairStylistRequestResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use App\Http\Responses\ApiResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Models\User; // Added to use the User model for finding the user

class HairStylistRequestController extends Controller
{
    protected $hairStylistRequestService;

    public function __construct(HairStylistRequestService $hairStylistRequestService)
    {
        $this->hairStylistRequestService = $hairStylistRequestService;
        $this->middleware('auth');
        // Other constructor code...
    }

    public function createHairStylistRequest(CreateHairStylistRequest $request): JsonResponse
    {
        $validatedData = $request->validated();
        // $validatedData['user_id'] = Auth::id(); // Ensure the user_id is the authenticated user's ID

        // Custom validation for user_id existence
        $validator = Validator::make($validatedData, [
            'user_id' => 'exists:users,id',
            'service_details' => 'required',
            'preferred_date' => 'required|date',
            'preferred_time' => 'required',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $hairStylistRequest = $this->hairStylistRequestService->createRequest($validatedData);

        return ApiResponse::stylistRequestCreated($hairStylistRequest);
    }

    public function sendRequestToStylist(CreateHairStylistRequest $request): JsonResponse
    {
        $validatedData = $request->validated();

        $user = User::find($validatedData['user_id']);
        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        try {
            $hairStylistRequest = $this->hairStylistRequestService->createRequest($validatedData);
            return ApiResponse::stylistRequestCreated(new HairStylistRequestResource($hairStylistRequest));
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    // ... other methods ...
}
