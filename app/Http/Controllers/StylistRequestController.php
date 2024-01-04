
<?php

use App\Http\Requests\CreateHairStylistRequest;
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
}
