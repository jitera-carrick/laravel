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
            'user_id' => 'required|integer|exists:users,id',
            'area' => 'required|array',
            'area.*' => 'integer|exists:request_areas,area_id',
            'menu' => 'required|array',
            'menu.*' => 'integer|exists:request_menus,menu_id',
            'gender' => 'required|string|in:male,female,other',
            'date_of_birth' => 'required|date|before:today',
            'display_name' => 'required|string|max:20',
            'hair_concerns' => 'nullable|string|max:2000',
            'image_paths' => 'nullable|array|max:3',
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
            'user_id.required' => 'The user id field is required.',
            'user_id.integer' => 'The user id must be an integer.',
            'user_id.exists' => 'The selected user id is invalid.',
            'area.required' => 'The area field is required.',
            'area.array' => 'The area must be an array.',
            'area.*.integer' => 'Each area must be an integer.',
            'area.*.exists' => 'The selected area is invalid.',
            'menu.required' => 'The menu field is required.',
            'menu.array' => 'The menu must be an array.',
            'menu.*.integer' => 'Each menu must be an integer.',
            'menu.*.exists' => 'The selected menu is invalid.',
            'gender.required' => 'The gender field is required.',
            'gender.in' => 'The selected gender is invalid.',
            'date_of_birth.required' => 'The date of birth field is required.',
            'date_of_birth.date' => 'The date of birth is not a valid date.',
            'date_of_birth.before' => 'The date of birth must be a date before today.',
            'display_name.required' => 'The display name field is required.',
            'display_name.max' => 'The display name may not be greater than 20 characters.',
            'hair_concerns.max' => 'The hair concerns may not be greater than 2000 characters.',
            'image_paths.array' => 'The image paths must be an array.',
            'image_paths.max' => 'The image paths may not have more than 3 items.',
            'image_paths.*.file' => 'Each image path must be a file.',
            'image_paths.*.mimes' => 'Each file must be of type: png, jpg, jpeg.',
            'image_paths.*.max' => 'Each file may not be greater than 5MB.',
        ];
    }
}
