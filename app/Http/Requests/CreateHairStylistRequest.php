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
        // Check if the user is authenticated
        if (!Auth::check()) {
            return false;
        }

        // Check if the user_id matches the authenticated user's id
        return Auth::id() == $this->get('user_id');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'details' => 'required|string', // Changed from 'required|text' to 'required|string'
            'status' => 'required|string|in:pending,approved,rejected', // Changed from 'sometimes' to 'required'
            'user_id' => 'required|exists:users,id',
            'request_image_id' => 'sometimes|exists:request_images,id', // Retained from existing code
            'area_id' => 'required|array|min:1',
            'menu_id' => 'required|array|min:1',
            'hair_concerns' => 'required|string|max:3000',
            'image_paths' => 'array|max:3',
            'image_paths.*' => 'sometimes|file|mimes:png,jpg,jpeg|max:5120', // Added 'sometimes' from existing code
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
            'details.required' => 'The details field is required.',
            'status.required' => 'The status field is required and must be one of the valid options: pending, approved, rejected.',
            'user_id.required' => 'The user_id field is required and must exist in the users table.',
            'request_image_id.sometimes' => 'The request image id field is optional but must exist in the request_images table if provided.', // Retained from existing code
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
            'image_paths.*.sometimes' => 'Each image path is optional.', // Retained from existing code
            'image_paths.*.file' => 'Each image path must be a file.',
            'image_paths.*.mimes' => 'Each file must be of type: png, jpg, jpeg.',
            'image_paths.*.max' => 'Each file may not be greater than 5MB.',
        ];
    }
}
