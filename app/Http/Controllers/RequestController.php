<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateHairStylistRequest;
use App\Models\Request;
use App\Models\RequestAreaSelection;
use App\Models\RequestMenuSelection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class RequestController extends Controller
{
    // ... other methods ...

    // Method to update a hair stylist request
    public function update(UpdateHairStylistRequest $request, $id): JsonResponse
    {
        $hairRequest = Request::find($id);
        $user = Auth::user();

        // Check if the hair stylist request exists and if the authenticated user is authorized to update it
        if (!$hairRequest || $hairRequest->user_id !== $user->id) {
            return response()->json(['message' => 'Request not found or unauthorized.'], 404);
        }

        // Validate the 'user_id' to ensure it matches the 'user_id' associated with the hair stylist request
        if ($request->has('user_id') && $request->user_id != $hairRequest->user_id) {
            return response()->json(['message' => 'The user_id does not match the request owner.'], 403);
        }

        // Validate the request parameters
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:requests,id',
            'area' => 'nullable|array',
            'area.*' => 'exists:request_areas,id', // Validate each area id in the array
            'menu' => 'nullable|array',
            'menu.*' => 'exists:request_menus,id', // Validate each menu id in the array
            'hair_concerns' => 'nullable|string|max:1000',
            'status' => 'nullable|string|in:pending,approved,rejected,cancelled', // Assuming these are the valid statuses
            'user_id' => 'required|integer|exists:users,id' // Validate the user_id
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        DB::beginTransaction();
        try {
            // Update area selections if provided
            if ($request->has('area')) {
                RequestAreaSelection::where('request_id', $id)->delete();
                foreach ($request->area as $areaId) {
                    RequestAreaSelection::create([
                        'request_id' => $id,
                        'area_id' => $areaId,
                    ]);
                }
            }

            // Update menu selections if provided
            if ($request->has('menu')) {
                RequestMenuSelection::where('request_id', $id)->delete();
                foreach ($request->menu as $menuId) {
                    RequestMenuSelection::create([
                        'request_id' => $id,
                        'menu_id' => $menuId,
                    ]);
                }
            }

            // Update hair concerns if provided
            if ($request->has('hair_concerns')) {
                $hairRequest->hair_concerns = $request->hair_concerns;
            }

            // Update status if provided
            if ($request->has('status')) {
                $hairRequest->status = $request->status;
            }

            // Set the 'updated_at' timestamp to the current time
            $hairRequest->updated_at = now();

            $hairRequest->save();

            DB::commit();

            return response()->json([
                'status' => 200,
                'request' => $hairRequest->fresh(),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to update the request.'], 500);
        }
    }

    // ... other methods ...
}
