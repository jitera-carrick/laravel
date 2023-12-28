<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateRequestRequest;
use App\Models\Request;
use App\Models\RequestImage;
use App\Services\RequestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class RequestController extends Controller
{
    protected $requestService;

    public function __construct(RequestService $requestService)
    {
        $this->requestService = $requestService;
    }

    // ... other methods ...

    /**
     * Update an existing hair stylist request.
     *
     * @param UpdateRequestRequest $request
     * @param int $requestId
     * @return JsonResponse
     */
    public function updateRequest(UpdateRequestRequest $request, int $requestId): JsonResponse
    {
        $validatedData = $request->validated();
        $user = Auth::user();

        // Retrieve the request to be updated
        $hairRequest = Request::findOrFail($requestId);

        // Authorize the action using RequestPolicy
        $this->authorize('update', $hairRequest);

        // Ensure the request belongs to the logged-in user
        if ($hairRequest->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized to update this request'], 403);
        }

        // Begin transaction
        DB::beginTransaction();
        try {
            // Update area if provided
            if (isset($validatedData['area'])) {
                $hairRequest->area = $validatedData['area'];
            }

            // Update menu if provided
            if (isset($validatedData['menu'])) {
                $hairRequest->menu = $validatedData['menu'];
            }

            // Update hair_concerns if provided and valid
            if (isset($validatedData['hair_concerns'])) {
                $validator = Validator::make($validatedData, [
                    'hair_concerns' => 'max:3000',
                ]);

                if ($validator->fails()) {
                    return response()->json(['message' => 'Hair concerns may not be greater than 3000 characters.'], 422);
                }

                $hairRequest->hair_concerns = $validatedData['hair_concerns'];
            }

            // Update image_paths if provided
            if (isset($validatedData['image_paths']) && is_array($validatedData['image_paths'])) {
                // Validate each image path
                foreach ($validatedData['image_paths'] as $imagePath) {
                    // Assuming a method exists to validate image paths
                    if (!$this->requestService->validateImagePath($imagePath)) {
                        return response()->json(['message' => 'Invalid image path provided.'], 422);
                    }
                }

                // Delete existing images
                RequestImage::where('request_id', $hairRequest->id)->delete();

                // Insert new image paths
                foreach ($validatedData['image_paths'] as $imagePath) {
                    RequestImage::create([
                        'request_id' => $hairRequest->id,
                        'path' => $imagePath,
                    ]);
                }
            }

            // Save the updated request
            $hairRequest->save();

            // Commit transaction
            DB::commit();

            // Prepare the response data
            $responseData = [
                'request_id' => $hairRequest->id,
                'status' => $hairRequest->status,
                'message' => 'Request updated successfully.'
            ];

            return response()->json($responseData);
        } catch (\Exception $e) {
            // Rollback transaction on error
            DB::rollBack();
            return response()->json(['message' => 'An error occurred while updating the request.'], 500);
        }
    }
}
