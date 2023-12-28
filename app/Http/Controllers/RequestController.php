<?php

namespace App\Http\Controllers;

use App\Http\Requests\CancelRequest;
use App\Models\Request;
use App\Services\RequestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Exception;

class RequestController extends Controller
{
    protected $requestService;

    public function __construct(RequestService $requestService)
    {
        $this->requestService = $requestService;
    }

    // ... other methods ...

    public function cancelRequest(CancelRequest $formRequest): JsonResponse
    {
        try {
            $validatedData = $formRequest->validated();
            $requestId = $validatedData['request_id'];
            $userId = $validatedData['user_id'];

            // Check if the user_id corresponds to a logged-in customer
            if (Auth::check() && Auth::id() == $userId) {
                $request = Request::where('id', $requestId)
                                  ->where('user_id', $userId)
                                  ->firstOrFail();

                $request->status = 'canceled';
                $request->save();

                return response()->json([
                    'message' => 'Request has been successfully canceled.',
                    'request_id' => $request->id,
                    'status' => $request->status,
                ]);
            } else {
                return response()->json([
                    'error' => 'Unauthorized action or user not logged in.',
                ], 403);
            }
        } catch (Exception $e) {
            return response()->json([
                'error' => 'An error occurred while canceling the request.',
            ], 500);
        }
    }
}
