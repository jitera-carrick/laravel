<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateHairStylistRequest;
use App\Http\Requests\UpdateHairStylistRequest;
use App\Http\Resources\HairStylistRequestResource;
use App\Models\Request;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Validator;

class HairStylistRequestController extends Controller
{
    // Existing methods...

    public function store(CreateHairStylistRequest $request)
    {
        // Check if the user exists
        $user = User::find($request->user_id);
        if (!$user) {
            throw new ModelNotFoundException('User not found.');
        }

        // Create a new Request instance with the validated data
        $hairStylistRequest = new Request([
            'area' => $request->area,
            'menu' => $request->menu,
            'hair_concerns' => $request->hair_concerns,
            'user_id' => $request->user_id,
            'status' => 'pending', // default status
        ]);

        // Save the new Request instance to the database
        $hairStylistRequest->save();

        // Return a response with the newly created request
        return new HairStylistRequestResource($hairStylistRequest);
    }

    public function update(HttpRequest $httpRequest, $id)
    {
        // Validate the ID and request parameters
        $validator = Validator::make($httpRequest->all() + ['id' => $id], [
            'id' => 'required|integer',
            'area' => 'required|string',
            'menu' => 'required|string',
            'hair_concerns' => 'required|string',
        ]);

        if ($validator->fails()) {
            return new JsonResponse(['message' => $validator->errors()->first()], 422);
        }

        // Find the request by ID
        $hairStylistRequest = Request::find($id);
        if (!$hairStylistRequest) {
            return new JsonResponse(['message' => 'Request not found.'], 404);
        }

        // Check if the authenticated user can update the request
        if ($httpRequest->user()->cannot('update', $hairStylistRequest)) {
            return new JsonResponse(['message' => 'Forbidden'], 403);
        }

        // Update the request with validated data
        $hairStylistRequest->fill($httpRequest->only(['area', 'menu', 'hair_concerns']));
        $hairStylistRequest->save();

        // Return the updated request
        return new HairStylistRequestResource($hairStylistRequest);
    }

    // Other methods...
}
