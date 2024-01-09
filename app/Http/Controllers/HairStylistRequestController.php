<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateHairStylistRequest;
use App\Http\Requests\HairStylistRequestFilterRequest; // Import the request for filtering
use App\Services\HairStylistRequestService;
use App\Http\Resources\HairStylistRequestResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Models\HairStylistRequest; // Import the HairStylistRequest model

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

        try {
            // Validate the request against the requirements
            $validator = Validator::make($validatedData, [
                'service_details' => 'required|string',
                'preferred_date' => 'required|date',
                'preferred_time' => 'required|string',
                'user_id' => 'required|exists:users,id',
            ]);

            if ($validator->fails()) {
                return response()->json(['message' => $validator->errors()->first()], 400);
            }

            $hairStylistRequest = $this->hairStylistRequestService->createHairStylistRequest($validatedData);
            $resource = new HairStylistRequestResource($hairStylistRequest);
            return response()->json(['status' => 201, 'hair_stylist_request' => $resource], 201);
        } catch (ValidationException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function filterHairStylistRequests(HairStylistRequestFilterRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $query = HairStylistRequest::query();

        foreach ($validated as $field => $value) {
            if (!empty($value)) {
                $query->where($field, $value);
            }
        }

        $hairStylistRequests = $query->paginate($validated['limit'] ?? 15, ['*'], 'page', $validated['page'] ?? 1);
        return response()->json($hairStylistRequests);
    }

    // ... other methods ...
}
