
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
        $this->middleware('auth:sanctum');
    }

    public function createHairStylistRequest(CreateHairStylistRequest $request): JsonResponse
    {
        $validatedData = $request->validated();
        // The user_id is now being validated by the CreateHairStylistRequest, so no need to set it here

        // Custom validation for user_id existence is not needed as it's handled by CreateHairStylistRequest
        try {
            // Validate the request against the requirements
            $validator = Validator::make($validatedData, [
                'service_details' => 'required|string',
                'preferred_date' => 'required|date',
                'preferred_time' => 'required|string',
            ]);

            $hairStylistRequest = $this->hairStylistRequestService->createHairStylistRequest($validatedData);
            return new HairStylistRequestResource($hairStylistRequest);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    // ... other methods ...
}
