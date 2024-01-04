
<?php

use App\Http\Requests\CreateHairStylistRequest;
use App\Models\StylistRequest;
use App\Models\RequestImage; // Added line
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

    /**
     * Delete an image from a hair stylist request.
     *
     * @param int $request_id
     * @param int $image_id
     * @return JsonResponse
     */
    public function deleteImage(int $request_id, int $image_id): JsonResponse
    {
        try {
            $image = RequestImage::where('id', $image_id)->where('request_id', $request_id)->firstOrFail();

            // Authorization check (assuming a policy is defined for RequestImage)
            $this->authorize('delete', $image);

            $image->delete();

            return response()->json(['message' => 'Image deleted successfully.']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while deleting the image.'], 500);
        }
    }

    // ... other methods ...
}
