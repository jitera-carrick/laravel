<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreHairStylistRequest;
use App\Http\Requests\UpdateHairStylistRequest;
use App\Models\Request;
use App\Models\RequestArea;
use App\Models\RequestMenu;
use App\Models\Image;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon; // Import Carbon for date handling

class HairStylistRequestController extends Controller
{
    // ... other methods ...

    // Add the new autoExpireRequests method
    public function autoExpireRequests(): JsonResponse
    {
        // ... existing autoExpireRequests method ...
    }

    public function storeHairStylistRequest(StoreHairStylistRequest $request): JsonResponse
    {
        // ... existing storeHairStylistRequest method ...
    }

    // ... other methods ...

    public function cancelHairStylistRequest($id): JsonResponse
    {
        // ... existing cancelHairStylistRequest method ...
    }

    // ... other methods ...

    // This method has been renamed from validateHairStylistRequest to validateHairStylistRequestInput to resolve the conflict
    public function validateHairStylistRequestInput(HttpRequest $request): JsonResponse
    {
        // ... existing validateHairStylistRequestInput method ...
    }

    // ... other methods ...

    public function editRequest(UpdateHairStylistRequest $request, $requestId): JsonResponse
    {
        // ... existing editRequest method ...
    }

    // ... other methods ...

    public function updateHairStylistRequest(UpdateHairStylistRequest $request, $id): JsonResponse
    {
        // ... existing updateHairStylistRequest method ...
    }

    // ... other methods ...

    // New method deleteHairStylistRequest as per the guideline
    public function deleteHairStylistRequest(HttpRequest $request, $requestId): JsonResponse
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthorized. User is not authenticated.'
            ], 401);
        }

        $hairStylistRequest = Request::where('id', $requestId)
                                     ->where('user_id', $user->id)
                                     ->first();

        if (!$hairStylistRequest) {
            return response()->json([
                'message' => 'Request not found or does not belong to the user.'
            ], 404);
        }

        try {
            DB::beginTransaction();

            // Delete related areas, menus, and images
            $hairStylistRequest->requestAreas()->delete();
            $hairStylistRequest->requestMenus()->delete();
            $hairStylistRequest->images()->delete();

            // Delete the request itself
            $hairStylistRequest->delete();

            DB::commit();

            return response()->json([
                'message' => 'Hair stylist request deleted successfully.'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to delete hair stylist request.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // ... other methods ...
}
