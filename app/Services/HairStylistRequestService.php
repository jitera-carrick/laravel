
<?php

namespace App\Services;

use App\Models\HairStylistRequest;
use App\Models\User;
use App\Models\RequestImage;
// use Illuminate\Support\Facades\Log; // Uncomment this line if you want to use Laravel's logging facilities

class HairStylistRequestService
{
    public function createRequest($data)
    {
        // Check if the user_id corresponds to a valid user
        $user = User::find($data['user_id']);
        if (!$user) {
            throw new \Exception('Invalid user_id provided');
        }

        // If a request_image_id is provided, verify it
        if (isset($data['request_image_id'])) {
            $requestImage = RequestImage::find($data['request_image_id']);
            if (!$requestImage) {
                throw new \Exception('Invalid request_image_id provided');
            }
        }

        // Create a new HairStylistRequest record
        $hairStylistRequest = HairStylistRequest::create($data);

        return $hairStylistRequest;
    }

    public function cancelRequest(int $id)
    {
        try {
            $hairStylistRequest = HairStylistRequest::findOrFail($id);
            $hairStylistRequest->status = 'cancelled';
            $hairStylistRequest->updated_at = now();
            $hairStylistRequest->save();

            return ['success' => true, 'message' => 'Request cancelled successfully.'];
        } catch (\Exception $e) {
            // Log the exception and return an appropriate error message
            // Log::error($e->getMessage());
            throw new \Exception('Unable to cancel the request: ' . $e->getMessage());
        }
    }

    // Other methods...
}
