<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateHairStylistRequest;
use App\Http\Requests\UpdateHairStylistRequest;
use App\Http\Requests\DeleteHairStylistRequestImageRequest;
use App\Http\Requests\DeleteRequestImageRequest; // Added import for DeleteRequestImageRequest
use App\Services\HairStylistRequestService;
use App\Services\RequestImageService; // Added import for RequestImageService
use App\Http\Resources\HairStylistRequestResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request as HttpRequest;
use App\Exceptions\CustomException;
use App\Models\HairStylistRequest; // Added import for HairStylistRequest model
use Illuminate\Support\Facades\Auth; // Added import for Auth facade
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class HairStylistRequestController extends Controller
{
    protected $hairStylistRequestService;
    protected $requestImageService; // Added property for RequestImageService

    public function __construct(HairStylistRequestService $hairStylistRequestService, RequestImageService $requestImageService = null)
    {
        $this->hairStylistRequestService = $hairStylistRequestService;
        $this->requestImageService = $requestImageService; // Initialize RequestImageService if provided
    }

    public function createHairStylistRequest(CreateHairStylistRequest $request): JsonResponse
    {
        $validatedData = $request->validated();
        $hairStylistRequest = $this->hairStylistRequestService->createRequest($validatedData);

        return response()->json(new HairStylistRequestResource($hairStylistRequest), 201);
    }

    public function updateHairStylistRequest(UpdateHairStylistRequest $request): JsonResponse
    {
        $validatedData = $request->validated();
        $hairStylistRequest = $this->hairStylistRequestService->updateRequest(
            $validatedData['id'],
            $validatedData['user_id'],
            $validatedData['status']
        );

        return response()->json([
            'data' => new HairStylistRequestResource($hairStylistRequest),
            'message' => 'Hair stylist request updated successfully.'
        ]);
    }

    public function createOrUpdateHairStylistRequest(HttpRequest $request): JsonResponse
    {
        if ($request->has('id')) {
            $updateRequest = new UpdateHairStylistRequest();
            $updateRequest->setValidator($this->getValidationFactory()->make(
                $request->all(),
                $updateRequest->rules(),
                $updateRequest->messages(),
                $updateRequest->attributes()
            ));
            if ($updateRequest->fails()) {
                return response()->json(['message' => 'Validation failed.', 'errors' => $updateRequest->errors()], 422);
            }
            $validatedData = $updateRequest->validated();
            $hairStylistRequest = $this->hairStylistRequestService->updateRequest(
                $validatedData['id'],
                $validatedData['user_id'],
                $validatedData['status']
            );
        } else {
            $createRequest = new CreateHairStylistRequest();
            $createRequest->setValidator($this->getValidationFactory()->make(
                $request->all(),
                $createRequest->rules(),
                $createRequest->messages(),
                $createRequest->attributes()
            ));
            if ($createRequest->fails()) {
                return response()->json(['message' => 'Validation failed.', 'errors' => $createRequest->errors()], 422);
            }
            $validatedData = $createRequest->validated();
            $hairStylistRequest = $this->hairStylistRequestService->createRequest($validatedData);
        }
        return response()->json(['message' => 'Hair stylist request processed successfully.', 'data' => new HairStylistRequestResource($hairStylistRequest)], 200);
    }

    public function update(UpdateHairStylistRequest $request, $id): JsonResponse
    {
        $hairStylistRequest = HairStylistRequest::find($id);

        if (!$hairStylistRequest) {
            return response()->json(['message' => 'The request does not exist.'], 404);
        }

        if ($hairStylistRequest->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $validatedData = $request->validated();
        $hairStylistRequest->update($validatedData);

        return response()->json([
            'status' => 200,
            'hair_stylist_request' => new HairStylistRequestResource($hairStylistRequest)
        ], 200);
    }

    public function deleteHairStylistRequestImages(DeleteHairStylistRequestImageRequest $request): JsonResponse
    {
        $validatedData = $request->validated();
        $hairStylistRequestId = $validatedData['id'];

        try {
            $this->hairStylistRequestService->deleteImagesByHairStylistRequestId($hairStylistRequestId);
            return response()->json(['message' => 'Images deleted successfully.'], 200);
        } catch (CustomException $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred while deleting images.'], 500);
        }
    }

    public function deleteHairStylistRequestImage(DeleteRequestImageRequest $request): JsonResponse
    {
        $validatedData = $request->validated();
        $hairStylistRequestId = $validatedData['hair_stylist_request_id'];
        $imagePath = $validatedData['image_path'];

        $hairStylistRequest = HairStylistRequest::findOrFail($hairStylistRequestId);
        if ($hairStylistRequest->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized to delete image for this request.'], 403);
        }

        try {
            $this->requestImageService->deleteImageByPath($hairStylistRequestId, $imagePath);
            return response()->json(['message' => 'Hair stylist request image has been successfully deleted.'], 200);
        } catch (CustomException $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred while deleting the image.'], 500);
        }
    }

    // ... other methods ...
}
