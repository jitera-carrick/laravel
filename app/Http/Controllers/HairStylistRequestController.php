<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateHairStylistRequest;
use App\Services\HairStylistRequestService;
use App\Http\Resources\HairStylistRequestResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

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
        $validatedData['user_id'] = Auth::id(); // Ensure the user_id is the authenticated user's ID

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

        try {
            $hairStylistRequest = $this->hairStylistRequestService->createRequest($validatedData);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }

        return response()->json(new HairStylistRequestResource($hairStylistRequest), 201);
    }

    // ... other methods ...
}
