
<?php

namespace App\Services;

use App\Models\HairStylistRequest;
use App\Models\User;
use App\Models\RequestImage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class HairStylistRequestService
{
    public function createRequest($data)
    {
        // Check if the user_id corresponds to a valid user
        $user = User::find($data['user_id']);
        if (!$user) {
            throw new \Exception('Invalid user_id provided');
        }

        // Ensure 'service_details' is not empty
        if (empty($data['service_details'])) {
            throw new \Exception('Service details cannot be empty');
        }

        // Validate 'preferred_date' and 'preferred_time'
        $currentDate = new \DateTime();
        $preferredDate = new \DateTime($data['preferred_date']);
        if ($preferredDate <= $currentDate) {
            throw new \Exception('Preferred date cannot be in the past');
        }

        // Set the initial status of the request if not provided
        $data['status'] = $data['status'] ?? 'pending';

        // If a request_image_id is provided, verify it
        if (!empty($data['request_image_id'])) {
            $requestImage = RequestImage::find($data['request_image_id']);
            if (!$requestImage) {
                throw new \Exception('Invalid request_image_id provided');
            }
        } else {
            $data['request_image_id'] = null;
        }

        // Create a new HairStylistRequest record
        $hairStylistRequest = HairStylistRequest::create($data);

        return $hairStylistRequest;
    } // End of createRequest method

    // This method is not needed as createRequest method already exists and fulfills the requirement
    public function createHairStylistRequest($validatedData)
    {
        // Check if the user_id exists in the users table
        if (!User::find($validatedData['user_id'])) {
            throw new \Exception('Invalid user_id provided');
        }

        // Set the status to "pending" if not provided
        $validatedData['status'] = $validatedData['status'] ?? 'pending'; // This line is redundant as the createRequest method already sets the status

        // Create and save the new HairStylistRequest
        return HairStylistRequest::create($validatedData);
    }

    public function filterRequests($filters)
    {
        // Validation logic
        $validator = Validator::make($filters, [
            'service_details' => 'sometimes|string|max:200',
            'preferred_date' => 'sometimes|date',
            'status' => ['sometimes', Rule::in(['pending', 'approved', 'rejected'])],
            'page' => 'sometimes|integer|min:1',
            'limit' => 'sometimes|integer',
        ]);

        if ($validator->fails()) {
            throw new \Exception($validator->errors()->first());
        }

        $query = HairStylistRequest::query();

        if (isset($filters['service_details'])) {
            $query->where('service_details', 'like', '%' . $filters['service_details'] . '%');
        }

        if (isset($filters['preferred_date'])) {
            $query->whereDate('preferred_date', $filters['preferred_date']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $page = $filters['page'] ?? 1;
        $limit = $filters['limit'] ?? 10;

        return $query->paginate($limit, ['*'], 'page', $page);
    }
}
    public function sendStylistRequest(int $userId): HairStylistRequest
    {
        $user = User::find($userId);
        if (!$user) {
            throw new \Exception('Invalid user_id provided');
        }

        $hairStylistRequest = new HairStylistRequest([
            'user_id' => $userId,
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $hairStylistRequest->save();

        return $hairStylistRequest;
    }

    public function createStylistRequest(int $userId): HairStylistRequest
    {
        $user = User::find($userId);
        if (!$user) {
            throw new \Exception('Invalid user_id provided');
        }

        $hairStylistRequest = new HairStylistRequest([
            'user_id' => $userId,
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $hairStylistRequest->save();

        return $hairStylistRequest;
    }
} // End of HairStylistRequestService class
