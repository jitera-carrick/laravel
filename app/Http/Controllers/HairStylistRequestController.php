<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateHairStylistRequest;
use App\Http\Requests\HairStylistRequestFilterRequest;
use App\Services\HairStylistRequestService;
use App\Http\Resources\HairStylistRequestResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Models\User;

class HairStylistRequestController extends Controller
{
    protected $hairStylistRequestService;

    public function __construct(HairStylistRequestService $hairStylistRequestService)
    {
        $this->middleware('auth'); // Ensure the user is authenticated
        $this->hairStylistRequestService = $hairStylistRequestService;
        $this->middleware('auth:sanctum');
    }

    public function createHairStylistRequest(CreateHairStylistRequest $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'service_details' => 'required|string',
            'preferred_date' => 'required|date',
            'preferred_time' => 'required|string',
            'user_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')
            ],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if (!Auth::check() || Auth::id() != $request->user_id) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        try {
            $validatedData = $validator->validated();
            $validatedData['status'] = 'pending'; // Set the status to 'pending' before creating the request
            $hairStylistRequest = $this->hairStylistRequestService->createHairStylistRequest($validatedData);

            return response()->json(new HairStylistRequestResource($hairStylistRequest), 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    // ... other methods ...
}
