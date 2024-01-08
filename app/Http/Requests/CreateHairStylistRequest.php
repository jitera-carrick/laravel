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
        // Check if the user is authenticated
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'email' => 'required|email|unique:users,email',
            'password' => ['required', 'string', 'min:8', 'confirmed', Rule::regex('/[A-Za-z]/'), Rule::regex('/[0-9]/'), Rule::regex('/[@$!%*#?&]/')],
            'details' => 'required|string',
            'status' => 'required|string|in:pending,approved,rejected',
            'user_id' => 'required|exists:users,id',
            'area_id' => 'required|array|min:1',
            'menu_id' => 'required|array|min:1',
            'hair_concerns' => 'required|string|max:3000',
            'image_paths' => 'array|max:3',
            'image_paths.*' => 'file|mimes:png,jpg,jpeg|max:5120', // 5MB
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
            'email.required' => 'The email field is required.',
            'email.email' => 'The email must be a valid email address.',
            'email.unique' => 'The email has already been taken.',
            'password.required' => 'The password field is required.',
            'password.string' => 'The password must be a string.',
            'password.min' => 'The password must be at least 8 characters.',
            'password.confirmed' => 'The password confirmation does not match.',
            'password.regex' => 'The password format is invalid.',
            'details.required' => 'The details field is required.',
            'status.required' => 'The status field is required and must be one of the valid options: pending, approved, rejected.',
            'user_id.required' => 'The user_id field is required and must exist in the users table.',
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
            'image_paths.*.file' => 'Each image path must be a file.',
            'image_paths.*.mimes' => 'Each file must be of type: png, jpg, jpeg.',
            'image_paths.*.max' => 'Each file may not be greater than 5MB.',
        ];
    }
}
