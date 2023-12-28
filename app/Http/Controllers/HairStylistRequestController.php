<?php

namespace App\Http\Controllers;

use App\Http\Requests\CancelHairStylistRequest;
use App\Models\Request as RequestModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class HairStylistRequestController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function destroy(CancelHairStylistRequest $request, $id): JsonResponse
    {
        try {
            $hairStylistRequest = RequestModel::findOrFail($id);

            if ($hairStylistRequest->user_id !== Auth::id()) {
                return response()->json(['message' => 'Unauthorized to cancel this request.'], 401);
            }

            $hairStylistRequest->delete();

            return response()->json(['status' => 200, 'message' => 'Request cancelled successfully.'], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Request not found.'], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred while cancelling the request.'], 500);
        }
    }
}
