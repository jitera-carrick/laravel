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
        $validatedData['user_id'] = Auth::id(); // Ensure the user_id is the authenticated user's ID

        try {
            // Validate the request against the requirements
            $validator = Validator::make($validatedData, [
                'service_details' => 'required|string',
                'preferred_date' => 'required|date',
                'preferred_time' => 'required|string',
                'user_id' => 'required|exists:users,id',
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $hairStylistRequest = $this->hairStylistRequestService->sendStylistRequest($validatedData['user_id']);
            return response()->json(new HairStylistRequestResource($hairStylistRequest), 200);
        } catch (ValidationException $e) {
            return response()->json(['message' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    // ... other methods ...

    /**
     * Handle the incoming request to create a hair stylist request.
     *
     * @param  \App\Http\Requests\CreateHairStylistRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(CreateHairStylistRequest $request)
    {
        try {
            $hairStylistRequest = app(HairStylistRequestService::class)->sendStylistRequest($request->validated()['user_id']);
            return new ApiResponse(new HairStylistRequestResource($hairStylistRequest), true, 'Hair stylist request created successfully.');
        } catch (\Exception $e) {
            return new ApiResponse(null, false, $e->getMessage());
        }
    }
}
