<?php

namespace App\Services;

use App\Models\HairStylistRequest;
use App\Models\User;
use App\Models\RequestImage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Pagination\Paginator;
use Illuminate\Validation\ValidationException;

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

        $hairStylistRequest->status = 'pending';
        $hairStylistRequest->created_at = now();
        $hairStylistRequest->updated_at = now();
        $hairStylistRequest->save();

        return $hairStylistRequest;
    }

    public function createHairStylistRequest($validatedData)
    {
        // Check if the user_id corresponds to a valid user
        $user = User::find($validatedData['user_id']);
        if (!$user) {
            throw new \Exception('Invalid user_id provided');
        }

        // Set the status to 'pending' and create the HairStylistRequest
        $validatedData['status'] = 'pending';
        $hairStylistRequest = HairStylistRequest::create($validatedData);
        $hairStylistRequest->created_at = now();
        $hairStylistRequest->updated_at = now();

        return $hairStylistRequest;
    }

    public function filterRequests($service_details, $preferred_date, $status, $page, $limit)
    {
        // Validate the input parameters
        $validator = Validator::make([
            'service_details' => $service_details,
            'preferred_date' => $preferred_date,
            'status' => $status,
            'page' => $page,
            'limit' => $limit
        ], [
            'service_details' => 'nullable|string|max:500',
            'preferred_date' => 'nullable|date',
            'status' => 'nullable|in:pending,approved,rejected,cancelled',
            'page' => 'nullable|integer|min:1',
            'limit' => 'nullable|integer'
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $query = HairStylistRequest::query();

        if ($service_details) {
            $query->where('service_details', 'like', '%' . $service_details . '%');
        }
        if ($preferred_date) {
            $query->where('preferred_date', $preferred_date);
        }
        if ($status) {
            $query->where('status', $status);
        }

        Paginator::currentPageResolver(function () use ($page) {
            return $page;
        });

        $paginatedResults = $query->paginate($limit);

        return $paginatedResults;
    }
}
