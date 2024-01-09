
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class CreateHairStylistRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Check if the user is authenticated and if the user_id matches the authenticated user's id
        return Auth::check() && Auth::id() == $this->get('user_id');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'user_id' => 'required|integer|exists:users,id',
            'service_details' => 'required|string',
            'preferred_date' => 'required|date|after_or_equal:today',
            'preferred_time' => 'required|string',
            'status' => 'sometimes|in:pending,approved,rejected',
            'request_image_id' => 'sometimes|exists:request_images,id',
        ];
    }

    /**
     * Get the custom messages for validation errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'user_id.required' => 'User not found.',
            'user_id.exists' => 'User not found.',
            'service_details.required' => 'Service details are required.',
            'preferred_date.required' => 'The preferred date field is required.',
            'preferred_date.date' => 'Invalid date format.',
            'preferred_date.after_or_equal' => 'The preferred date must be today or a future date.',
            'preferred_time.required' => 'Preferred time is required.',
            'status.required' => 'The status field is required and must be one of the valid options: pending, approved, rejected.',
            'request_image_id.sometimes' => 'The request image id field is optional but must exist in the request_images table if provided.',
        ];
    }
}
