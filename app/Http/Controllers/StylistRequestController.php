
<?php

namespace App\Http\Controllers;

use App\Http\Requests\CancelRequest;
use App\Http\Requests\CreateStylistRequest;
use App\Services\StylistRequestService;
use Illuminate\Http\JsonResponse;
use App\Models\StylistRequest; // Assuming the use of StylistRequest model

class StylistRequestController extends Controller
{
    protected $stylistRequestService;

    public function __construct(StylistRequestService $stylistRequestService)
    {
        $this->stylistRequestService = $stylistRequestService;
    }

    public function createStylistRequest(CreateStylistRequest $request): JsonResponse
    {
        $validatedData = $request->validated();
        $stylistRequestId = $this->stylistRequestService->createRequest($validatedData);

        return response()->json([
            'stylist_request_id' => $stylistRequestId,
            'message' => 'Stylist request created successfully.'
        ], 201);
    }

    public function cancelRequest(CancelRequest $request, int $id): JsonResponse
    {
        $validatedData = $request->validated();
        $userId = $validatedData['user_id'];

        if (!$this->authorize('cancel', [StylistRequest::class, $id, $userId])) {
            return response()->json([
                'message' => 'Unauthorized to cancel this request.'
            ], 403);
        }

        $stylistRequest = $this->stylistRequestService->cancelRequest($id, $userId);

        return response()->json([
            'message' => 'Stylist request cancelled successfully.',
            'request_id' => $stylistRequest->id,
            'status' => $stylistRequest->status
        ], 200);
    }

    // ... other methods ...
}
