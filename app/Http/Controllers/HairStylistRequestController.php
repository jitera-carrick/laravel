<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateHairStylistRequest;
use App\Http\Requests\HairStylistRequestFilterRequest;
use App\Services\HairStylistRequestService;
use App\Http\Resources\HairStylistRequestResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Models\HairStylistRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\User;

class HairStylistRequestController extends Controller
{
    protected $hairStylistRequestService;

    public function __construct(HairStylistRequestService $hairStylistRequestService)
    {
        $this->hairStylistRequestService = $hairStylistRequestService;
        $this->middleware('auth:sanctum')->except(['createHairStylistRequest']);
    }

    public function createHairStylistRequest(CreateHairStylistRequest $request): JsonResponse
    {
        $validatedData = $request->validated();
        try {
            // Custom validation messages
            $validator = Validator::make($validatedData, [
                'service_details' => 'required|string',
                'preferred_date' => 'required|date',
                'preferred_time' => 'required|string',
                'user_id' => 'required|exists:users,id',
            ], [
                'service_details.required' => 'Service details are required.',
                'preferred_date.date' => 'Invalid date format.',
                'preferred_date.required' => 'Preferred date is required.',
                'preferred_time.required' => 'Preferred time is required.',
                'user_id.exists' => 'User not found.',
            ]);

            if ($validator->fails()) {
                return response()->json(['message' => $validator->errors()->first()], 400);
            }

            $hairStylistRequest = $this->hairStylistRequestService->createRequest($validatedData);
            $resource = new HairStylistRequestResource($hairStylistRequest);

            return response()->json(['status' => 201, 'hair_stylist_request' => $resource], 201);
        } catch (ValidationException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
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
        $validatedData['user_id'] = Auth::id();

        try {
            $hairStylistRequest = $this->hairStylistRequestService->createStylistRequest($validatedData);
            return response()->json([
                'status' => 201,
                'hair_stylist_request' => new HairStylistRequestResource($hairStylistRequest)
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred while creating the hair stylist request.'], 500);
        }
    }

    // Removed the createStylistRequest method as it is redundant and does not meet the requirement.

    // Removed the __invoke method as it is redundant and does not meet the requirement.
}
