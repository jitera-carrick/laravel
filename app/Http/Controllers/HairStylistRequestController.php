<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateHairStylistRequest;
use App\Http\Requests\HairStylistRequestFilterRequest; // Import the request validation class
use App\Services\HairStylistRequestService;
use App\Http\Resources\HairStylistRequestResource;
use Illuminate\Http\JsonResponse;

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
        $validatedData['status'] = 'pending'; // Set the status to 'pending' before creating the request
        $hairStylistRequest = $this->hairStylistRequestService->createHairStylistRequest($validatedData);

        return response()->json(new HairStylistRequestResource($hairStylistRequest), 201);
    }

    public function filterHairStylistRequests(HairStylistRequestFilterRequest $request): JsonResponse
    {
        $validatedData = $request->validated();
        $filteredRequests = $this->hairStylistRequestService->filterRequests(
            $validatedData['service_details'] ?? null,
            $validatedData['preferred_date'] ?? null,
            $validatedData['status'] ?? null,
            $validatedData['page'] ?? null,
            $validatedData['limit'] ?? null
        );

        return response()->json($filteredRequests, 200);
    }

    // ... other methods ...
}
