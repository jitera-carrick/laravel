<?php

namespace App\Http\Controllers;

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

    // Method to update a hair stylist request
    public function update(UpdateHairStylistRequest $request, $id): JsonResponse
    {
        $hairRequest = Request::find($id);
        $user = Auth::user();

        if (!$hairRequest || $hairRequest->user_id !== $user->id) {
            return response()->json(['message' => 'Request not found or unauthorized.'], 404);
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
                // Assuming 'status' is a valid column in the 'requests' table and has predefined enum values
                $hairRequest->status = $request->status;
            }

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
