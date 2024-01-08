<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateHairStylistRequest;
use App\Http\Requests\CancelHairStylistRequest;
use App\Services\HairStylistRequestService;
use App\Http\Resources\HairStylistRequestResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Auth\Access\AuthorizationException;
use Exception;

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
        $hairStylistRequest = $this->hairStylistRequestService->createRequest($validatedData);

        return response()->json(new HairStylistRequestResource($hairStylistRequest), 201);
    }

    public function cancelHairStylistRequest(CancelHairStylistRequest $request): JsonResponse
    {
        try {
            $validatedData = $request->validated();
            $hairStylistRequest = $this->hairStylistRequestService->cancelRequest($validatedData['request_id']);

            return response()->json([
                'status' => 200,
                'message' => 'Hair stylist request canceled successfully.'
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Request not found.'], 404);
        } catch (AuthorizationException $e) {
            return response()->json(['error' => 'User is not authorized to cancel this request.'], 403);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function createOrUpdateHairStylistRequest(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'details' => 'required|string',
            'status' => [
                'sometimes',
                'string',
                Rule::in(['pending', 'approved', 'rejected']) // Assuming these are the predefined status values
            ],
            'user_id' => 'required|exists:users,id',
            'request_image_id' => 'sometimes|nullable|exists:request_images,id'
        ], [
            'details.required' => 'Details are required.',
            'user_id.exists' => 'User not found.',
            'request_image_id.exists' => 'Request image not found.',
            'status.in' => 'Invalid status value.'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $validatedData = $validator->validated();
        if (isset($validatedData['request_id'])) {
            $hairStylistRequest = $this->hairStylistRequestService->updateRequest($validatedData, $validatedData['request_id']);
            $status = 200;
        } else {
            $hairStylistRequest = $this->hairStylistRequestService->createRequest($validatedData);
            $status = 201;
        }

        return response()->json(new HairStylistRequestResource($hairStylistRequest), $status);
    }

    // ... other methods ...
}
