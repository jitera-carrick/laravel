
<?php

use App\Http\Requests\CreateHairStylistRequest;
use App\Http\Requests\UpdateHairStylistRequest;
use App\Models\StylistRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Event;
use App\Events\StylistRequestSubmitted;

namespace App\Http\Controllers;

class StylistRequestController extends Controller
{
    // ... other methods ...

    public function submitStylistRequest(CreateHairStylistRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $stylistRequest = new StylistRequest([
            'user_id' => $validated['user_id'],
            'request_time' => now(),
            'status' => 'pending',
        ]);

        $stylistRequest->save();

        Event::dispatch(new StylistRequestSubmitted($stylistRequest));

        return response()->json([
            'request_id' => $stylistRequest->id,
            'request_time' => $stylistRequest->request_time,
        ]);
    }

    // ... other methods ...

    public function updateStylistRequest(UpdateHairStylistRequest $request, StylistRequest $stylistRequest): JsonResponse
    {
        $validated = $request->validated();

        if (isset($validated['request_time'])) {
            $stylistRequest->request_time = $validated['request_time'];
        }

        if (isset($validated['status'])) {
            $stylistRequest->status = $validated['status'];
        }

        $stylistRequest->save();

        return response()->json([
            'message' => 'Stylist request updated successfully.',
        ]);
    }
}
