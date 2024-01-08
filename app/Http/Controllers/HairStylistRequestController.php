<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateHairStylistRequest;
use App\Http\Requests\DeleteHairStylistRequestImageRequest;
use App\Http\Requests\UpdateHairStylistRequest;
use App\Services\HairStylistRequestService;
use App\Http\Resources\HairStylistRequestResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request as HttpRequest;
use App\Exceptions\CustomException;

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
        $hairStylistRequest = $this->hairStylistRequestService->createRequest($validatedData);

        return response()->json(new HairStylistRequestResource($hairStylistRequest), 201);
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
            $validatedData = $updateRequest->validated();
            $hairStylistRequest = $this->hairStylistRequestService->updateHairStylistRequest($request->input('id'), $validatedData);
        } else {
            $createRequest = new CreateHairStylistRequest();
            $createRequest->setValidator($this->getValidationFactory()->make(
                $request->all(),
                $createRequest->rules(),
                $createRequest->messages(),
                $createRequest->attributes()
            ));
            $validatedData = $createRequest->validated();
            $hairStylistRequest = $this->hairStylistRequestService->createRequest($validatedData);
        }
        return response()->json(['message' => 'Hair stylist request processed successfully.', 'data' => new HairStylistRequestResource($hairStylistRequest)], 200);
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

    // ... other methods ...
}
