<?php

namespace App\Http\Controllers;

use App\Http\Resources\RequestResource;
use Illuminate\Http\Request as HttpRequest;
use App\Http\Requests\UpdateHairStylistRequest;
use App\Models\Request;
use App\Models\RequestAreaSelection;
use App\Models\RequestMenuSelection;
use App\Models\RequestImage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class RequestController extends Controller
{
    // ... other methods ...
    
    // Method to create a hair stylist request
    // ... existing code for createHairStylistRequest method ...

    // Method to update a hair stylist request with route model binding
    // ... existing code for updateHairStylistRequest method ...

    // Method to update a hair stylist request
    public function update(UpdateHairStylistRequest $request, $id): JsonResponse
    {
        $hairRequest = Request::find($id);
        $user = Auth::user();

        if (!$hairRequest || $hairRequest->user_id !== $user->id) {
            return response()->json(['message' => 'Request not found or unauthorized.'], 404);
        }

        // Validate the request
        $validator = Validator::make($request->all(), [
            'area' => 'sometimes|string',
            'menu' => 'sometimes|string',
            'hair_concerns' => 'sometimes|string|max:3000',
            'status' => 'sometimes|string|in:pending,completed,cancelled', // Assuming these are valid statuses
            'priority' => 'sometimes|string|in:low,normal,high', // Assuming these are valid priorities
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        // Update area and menu selections within a transaction
        DB::transaction(function () use ($request, $id, $hairRequest) {
            // Update area selections
            if ($request->has('area')) {
                RequestAreaSelection::where('request_id', $id)->delete();
                RequestAreaSelection::create([
                    'request_id' => $id,
                    'area_id' => $request->area,
                ]);
            }

            // Update menu selections
            if ($request->has('menu')) {
                RequestMenuSelection::where('request_id', $id)->delete();
                RequestMenuSelection::create([
                    'request_id' => $id,
                    'menu_id' => $request->menu,
                ]);
            }

            // Update hair concerns
            if ($request->has('hair_concerns')) {
                $hairRequest->update(['hair_concerns' => $request->hair_concerns]);
            }

            // Update status and priority if provided
            $hairRequest->fill($request->only(['status', 'priority']));
            $hairRequest->save();
        });

        return response()->json([
            'status' => 200,
            'message' => 'Hair stylist request updated successfully',
            'request' => new RequestResource($hairRequest->fresh()),
        ]);
    }

    // Method to delete an image from a hair stylist request
    // ... existing code for deleteImage method ...

    // ... other methods ...
}
