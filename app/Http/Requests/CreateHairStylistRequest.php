
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
            'user_id' => 'required|exists:users,id',
            'service_details' => 'required|string',
            'preferred_date' => 'required|date|after_or_equal:today',
            'preferred_time' => 'required|string',
            'status' => 'required|in:pending,approved,rejected',
            'request_image_id' => 'sometimes|exists:request_images,id', // Retained from existing code
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
            'user_id.required' => 'The user_id field is required and must exist in the users table.',
            'service_details.required' => 'The service details field is required.',
            'preferred_date.required' => 'The preferred date field is required.',
            'preferred_date.date' => 'The preferred date must be a valid date.',
            'preferred_date.after_or_equal' => 'The preferred date must be today or a future date.',
            'preferred_time.required' => 'The preferred time field is required.',
            'status.required' => 'The status field is required and must be one of the valid options: pending, approved, rejected.',
            'request_image_id.sometimes' => 'The request image id field is optional but must exist in the request_images table if provided.', // Retained from existing code
        ];
    }
}
