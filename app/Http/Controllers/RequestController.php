<?php

namespace App\Http\Controllers;

use App\Models\Request;
use App\Models\RequestArea;
use App\Models\RequestMenu;
use App\Models\Image;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request as HttpRequest;

class RequestController extends Controller
{
    // ... other methods ...

    /**
     * Cancel a stylist request.
     *
     * @param int $requestId
     * @return JsonResponse
     */
    public function cancelStylistRequest($requestId)
    {
        $user = auth()->user(); // Retrieve the currently authenticated user

        if (!$user || !$user->is_logged_in) {
            return response()->json(['error' => 'User must be logged in to cancel requests.'], 401);
        }

        DB::beginTransaction();
        try {
            $request = Request::with(['requestAreas', 'requestMenus', 'images'])->findOrFail($requestId);
            
            // Check if the request belongs to the logged-in user
            if ($request->user_id !== $user->id) {
                DB::rollBack(); // Ensure to rollback if not authorized
                return response()->json(['error' => 'You are not authorized to cancel this request.'], 403);
            }

            // Update the status of the request to 'canceled' instead of deleting it
            $request->status = 'canceled';
            $request->save();

            // Cascade delete related entries
            $request->requestAreas()->delete();
            $request->requestMenus()->delete();
            $request->images()->delete();

            DB::commit();

            return response()->json([
                'message' => 'Your request has been successfully canceled.',
                'request_id' => $request->id,
                'status' => $request->status
            ], 200);
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json(['error' => 'Request not found.'], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'An error occurred while canceling the request.'], 500);
        }
    }

    // ... other methods ...

    /**
     * Create a new stylist request.
     *
     * @param HttpRequest $httpRequest
     * @return JsonResponse
     */
    // ... The createStylistRequest method remains unchanged ...

    /**
     * Edit a stylist request.
     *
     * @param HttpRequest $request
     * @param int $requestId
     * @return JsonResponse
     */
    // ... The editStylistRequest method remains unchanged ...

    // ... other methods ...
}
