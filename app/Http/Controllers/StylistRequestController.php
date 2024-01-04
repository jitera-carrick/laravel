<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateHairStylistRequest;
use App\Models\StylistRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Event;
use App\Events\StylistRequestSubmitted;

class StylistRequestController extends Controller
{
    // ... other methods ...

    public function submitStylistRequest(CreateHairStylistRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = User::find($validated['user_id']);
        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        $stylistRequest = new StylistRequest([
            'user_id' => $user->id, // Ensuring we use the found user's ID
            'request_time' => now(),
            'status' => 'pending',
        ]);

        $stylistRequest->save();

        Event::dispatch(new StylistRequestSubmitted($stylistRequest));

        return response()->json([
            'status' => 200,
            'message' => 'Stylist request submitted successfully.',
            'request_id' => $stylistRequest->id,
        ]);
    }

    // ... other methods ...
}
