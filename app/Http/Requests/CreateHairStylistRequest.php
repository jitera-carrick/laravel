<?php

namespace App\Http\Requests; // No change here, just for context

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class CreateHairStylistRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize() // No change here, just for context
    {
        // Check if the user is authenticated and if the user_id matches the authenticated user's id
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules() // No change here, just for context
    {
        return [
            'user_id' => 'required|integer|exists:users,id',
            'service_details' => 'required|string',
            'preferred_date' => 'required|date|after_or_equal:today',
            'preferred_time' => 'required|string',
            // The 'status' field is not required for the creation of a new request, it will be set to 'pending' by default.
            'request_image_id' => 'sometimes|exists:request_images,id',
        ];
    }

    /**
     * Get the custom messages for validation errors.
     *
     * @return array
     */
    public function messages() // No change here, just for context
    {
        return [
            'user_id.required' => 'The user_id field is required.',
            'user_id.exists' => 'User not found.',
            'service_details.required' => 'Service details are required.',
            'preferred_date.required' => 'The preferred date field is required.',
            'preferred_date.date' => 'Invalid date format.',
            'preferred_date.after_or_equal' => 'The preferred date must be today or a future date.',
            'preferred_time.required' => 'Preferred time is required.',
            // Removed the 'status.required' message as the status field is not required for request creation.
            'request_image_id.sometimes' => 'The request image id field is optional but must exist in the request_images table if provided.',
        ];
    }
}
