<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CreateHairStylistRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Check if the user is authenticated and if the user_id matches the authenticated user's id for PATCH requests
        if ($this->isMethod('patch')) {
            return Auth::check() && Auth::id() == $this->get('user_id');
        }
        // For POST requests, just check if the user is authenticated
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'details' => 'required|string',
            'user_id' => 'required|exists:users,id',
            'request_image_id' => 'sometimes|exists:request_images,id',
            'area_id' => 'required|array|min:1',
            'menu_id' => 'required|array|min:1',
            'hair_concerns' => 'required|string|max:3000',
            'image_paths' => 'array|max:3',
            'image_paths.*' => 'sometimes|file|mimes:png,jpg,jpeg|max:5120',
        ];

        if ($this->isMethod('patch')) {
            $rules['user_id'] = ['required', 'integer', 'exists:users,id'];
            $rules['status'] = ['required', Rule::in(['pending', 'approved', 'rejected'])]; // Assuming 'pending', 'approved', 'rejected' are the valid enum values
        }

        return $rules;
    }

    /**
     * Get the custom messages for validation errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'details.required' => 'The details field is required.',
            'user_id.required' => 'The user_id field is required and must exist in the users table.',
            'user_id.integer' => 'The user ID must be a valid integer.', // For PATCH only
            'user_id.exists' => 'The user does not exist.', // For PATCH only
            'status.required' => 'The status field is required and must be a valid value from the StatusEnum.', // For PATCH only
            'status.in' => 'Invalid status value.', // For PATCH only
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
        ];
    }
}
