<?php

namespace App\Services;

use App\Models\HairStylistRequest;
use App\Models\User;
use App\Models\RequestImage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class HairStylistRequestService
{
    // This method has been updated to createHairStylistRequest and now only handles the creation logic
    public function createHairStylistRequest($validatedData)
    {
        // Create a new HairStylistRequest model instance with the provided data
        $hairStylistRequest = HairStylistRequest::create($validatedData);

        // Save the new hair stylist request to the database
        $hairStylistRequest->save();

        // Return the ID of the newly created hair stylist request
        return $hairStylistRequest->id;
    }

    public function create($data)
    {
        // Validate the data
        $validator = Validator::make($data, [
            'user_id' => 'required|exists:users,id',
            'requested_date' => 'required|date',
            'service_type' => ['required', Rule::in(HairStylistRequest::SERVICE_TYPES)],
            'status' => ['required', Rule::in(HairStylistRequest::STATUSES)],
            'additional_notes' => 'sometimes|max:1000',
        ]);

        if ($validator->fails()) {
            throw new \Exception($validator->errors()->first());
        }

        // Create a new HairStylistRequest record with the validated data
        $hairStylistRequest = HairStylistRequest::create($validator->validated());

        return $hairStylistRequest;
    }
}
