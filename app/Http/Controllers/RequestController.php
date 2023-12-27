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
        DB::beginTransaction();
        try {
            $request = Request::with(['requestAreas', 'requestMenus', 'images'])->findOrFail($requestId);
            
            // Cascade delete related entries
            $request->requestAreas()->delete();
            $request->requestMenus()->delete();
            $request->images()->delete();
            
            $request->delete();
            DB::commit();

            return response()->json(['message' => 'Request registration has been canceled.'], 200);
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
