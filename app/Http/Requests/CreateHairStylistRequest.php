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
            'area_ids' => 'required|array',
            'area_ids.*' => 'integer|exists:areas,id',
            'menu_ids' => 'required|array',
            'menu_ids.*' => 'integer|exists:menus,id',
            'hair_concerns' => 'string|max:3000',
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
            'user_id.required' => 'The user id field is required.',
            'user_id.integer' => 'The user id must be an integer.',
            'user_id.exists' => 'The selected user id is invalid.',
            'area_ids.required' => 'The area ids field is required.',
            'area_ids.array' => 'The area ids must be an array.',
            'area_ids.*.integer' => 'Each area id must be an integer.',
            'area_ids.*.exists' => 'The selected area id is invalid.',
            'menu_ids.required' => 'The menu ids field is required.',
            'menu_ids.array' => 'The menu ids must be an array.',
            'menu_ids.*.integer' => 'Each menu id must be an integer.',
            'menu_ids.*.exists' => 'The selected menu id is invalid.',
            'hair_concerns.max' => 'The hair concerns may not be greater than 3000 characters.',
            'image_paths.array' => 'The image paths must be an array.',
            'image_paths.max' => 'The image paths may not have more than 3 items.',
            'image_paths.*.file' => 'Each image path must be a file.',
            'image_paths.*.mimes' => 'Each file must be of type: png, jpg, jpeg.',
            'image_paths.*.max' => 'Each file may not be greater than 5MB.',
        ];
    }
}
