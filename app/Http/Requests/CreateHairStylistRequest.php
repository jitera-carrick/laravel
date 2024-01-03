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
        // Allow all users to make this request
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'area' => 'required|string', // Updated to string as per requirement
            'gender' => 'required|string|in:male,female,do_not_answer', // Updated enum values as per requirement
            'birth_date' => 'required|date|before:today', // Renamed from date_of_birth to birth_date as per requirement
            'display_name' => 'required|string|max:20',
            'menu' => 'required|string', // Updated to string as per requirement
            'hair_concerns' => 'required|string|max:2000',
            'images' => 'nullable|array|max:3', // Renamed from image_paths to images as per requirement
            'images.*' => 'file|mimes:png,jpg,jpeg|max:5120', // Updated validation for images as per requirement
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
            'area.required' => 'Area selection is required.',
            'gender.required' => 'Gender selection is required.',
            'gender.in' => 'The selected gender is invalid.',
            'birth_date.required' => 'Birth date is required.',
            'birth_date.date' => 'The birth date is not a valid date.',
            'birth_date.before' => 'The birth date must be a date before today.',
            'display_name.required' => 'The display name field is required.',
            'display_name.max' => 'Display name cannot exceed 20 characters.',
            'menu.required' => 'Menu selection is required.',
            'hair_concerns.required' => 'Hair concerns cannot exceed 2000 characters.',
            'images.array' => 'The images must be an array.',
            'images.max' => 'The images may not have more than 3 items.',
            'images.*.file' => 'Each image must be a file.',
            'images.*.mimes' => 'Invalid image format or size.',
            'images.*.max' => 'Each file may not be greater than 5MB.',
        ];
    }
}
