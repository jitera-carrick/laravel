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
        if (!$hairRequest) {
            return response()->json(['message' => 'The hair stylist request is not found.'], 404);
        }
        if ($hairRequest->user_id !== $user->id && !$user->isAdmin()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        // Validate the request parameters
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:requests,id',
            'area' => 'nullable|string|max:255',
            'menu' => 'nullable|string|max:255',
            'hair_concerns' => 'nullable|string|max:1000',
            'status' => 'nullable|string|in:pending,approved,rejected,cancelled',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        DB::beginTransaction();
        try {
            // Update area if provided
            if ($request->has('area')) {
                $hairRequest->area = $request->area;
            }

            // Update menu if provided
            if ($request->has('menu')) {
                $hairRequest->menu = $request->menu;
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
