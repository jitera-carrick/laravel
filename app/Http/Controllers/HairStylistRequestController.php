<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateHairStylistRequest;
use App\Services\HairStylistRequestService;
use App\Http\Resources\HairStylistRequestResource;
use Illuminate\Support\Facades\Auth;
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
        $this->middleware('auth:sanctum');
    }

    public function createHairStylistRequest(CreateHairStylistRequest $request): JsonResponse
    {
        $validatedData = $request->validated();
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
