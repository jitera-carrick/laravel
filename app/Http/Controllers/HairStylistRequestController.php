<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateHairStylistRequest;
use App\Services\HairStylistRequestService;
use App\Http\Resources\HairStylistRequestResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class HairStylistRequestController extends Controller
{
    protected $hairStylistRequestService;

    public function __construct(HairStylistRequestService $hairStylistRequestService)
    {
        $this->hairStylistRequestService = $hairStylistRequestService;
    }

    public function createHairStylistRequest(CreateHairStylistRequest $request): JsonResponse
    {
        $validatedData = $request->validated();
        $validatedData['user_id'] = $request->user()->id; // Set the user_id to the authenticated user's ID
        $hairStylistRequest = $this->hairStylistRequestService->createRequest($validatedData);

        return response()->json(new HairStylistRequestResource($hairStylistRequest), Response::HTTP_CREATED);
    }

    // ... other methods ...
}
