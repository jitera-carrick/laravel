<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateHairStylistRequest;
use App\Services\HairStylistRequestService;
use App\Http\Resources\HairStylistRequestResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

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

        $validatedData = $request->validated();
        $hairStylistRequest = $this->hairStylistRequestService->createHairStylistRequest($validatedData);

        return response()->json(new HairStylistRequestResource($hairStylistRequest), 201);
    }

    // ... other methods ...
}
