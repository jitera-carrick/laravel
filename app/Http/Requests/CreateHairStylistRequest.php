
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
            // Existing rules are retained...
            'user_id' => 'required|exists:users,id',
            'service_details' => 'required|string',
            'preferred_date' => 'required|date',
            'preferred_time' => 'required|date_format:H:i',
            // Retain other existing rules...
            'area_id' => 'required|array|min:1',
            'menu_id' => 'required|array|min:1',
            'hair_concerns' => 'required|string|max:3000',
            'image_paths' => 'array|max:3',
            'image_paths.*' => 'sometimes|file|mimes:png,jpg,jpeg|max:5120', // Combined 'sometimes' from existing code and validation rules from new code
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
            // Retain existing custom messages...
            'details.required' => 'The details field is required.',
            'status.required' => 'The status field is required and must be one of the valid options: pending, approved, rejected.',
            'user_id.required' => 'The user_id field is required and must exist in the users table.',
            'request_image_id.sometimes' => 'The request image id field is optional but must exist in the request_images table if provided.',
            'area_id.required' => 'The area selection is required.',
            'area_id.array' => 'The area selection must be an array.',
            'area_id.min' => 'At least one area must be selected.',
            'menu_id.required' => 'The menu selection is required.',
            'menu_id.array' => 'The menu selection must be an array.',
            'menu_id.min' => 'At least one menu item must be selected.',
            'hair_concerns.required' => 'Hair concerns are required.',
            'hair_concerns.max' => 'The hair concerns may not be greater than 3000 characters.',
            'image_paths.array' => 'The image paths must be an array.',
            'image_paths.max' => 'The image paths may not have more than 3 items.',
            'image_paths.*.sometimes' => 'Each image path is optional.',
            'image_paths.*.file' => 'Each image path must be a file.',
            'image_paths.*.mimes' => 'Each file must be of type: png, jpg, jpeg.',
            'image_paths.*.max' => 'Each file may not be greater than 5MB.',
            'service_details.required' => 'The service details field is required.',
            'preferred_date.required' => 'The preferred date field is required.',
            'preferred_time.required' => 'The preferred time field is required.',
            'preferred_time.date_format' => 'The preferred time must be in the format of hours and minutes (HH:MM).',
        ];
    }
}
