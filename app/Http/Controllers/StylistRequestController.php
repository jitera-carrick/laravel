
<?php

use App\Http\Requests\CreateHairStylistRequest;
use App\Models\StylistRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Event;
use App\Events\StylistRequestSubmitted; // Added as per patch

namespace App\Http\Controllers;

class StylistRequestController extends Controller
{
    // ... other methods ...

    public function submitStylistRequest(CreateHairStylistRequest $request): JsonResponse
    {
        // Ensure the 'use App\Events\StylistRequestSubmitted;' is added at the top of the file
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
}
