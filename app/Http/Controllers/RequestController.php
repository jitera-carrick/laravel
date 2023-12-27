<?php

namespace App\Http\Controllers;

use App\Models\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

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

        DB::beginTransaction();
        try {
            $request = Request::with(['requestAreas', 'requestMenus', 'images'])->findOrFail($requestId);
            
            // Check if the request belongs to the logged-in user
            if ($request->user_id !== $user->id) {
                DB::rollBack(); // Ensure to rollback if not authorized
                return response()->json(['error' => 'You are not authorized to cancel this request.'], 403);
            }

            // Cascade delete related entries
            $request->requestAreas()->delete();
            $request->requestMenus()->delete();
            $request->images()->delete();
            
            $request->delete();
            DB::commit();

            return response()->json(['message' => 'Your request registration has been successfully canceled.'], 200);
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json(['error' => 'Request not found.'], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'An error occurred while canceling the request.'], 500);
        }
    }

    // ... other methods ...
}
