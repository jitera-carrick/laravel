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
            'user_id' => 'required|integer|exists:users,id',
            // Combining the 'area' and 'area_ids' fields
            'area' => 'sometimes|required|string',
            'area_ids' => [
                'sometimes',
                'required',
                'array',
                'min:1',
                Rule::exists('areas', 'id')->whereNull('deleted_at'), // Assuming soft deletes
            ],
            // Combining the 'menu' and 'menu_ids' fields
            'menu' => 'sometimes|required|string',
            'menu_ids' => [
                'sometimes',
                'required',
                'array',
                'min:1',
                Rule::exists('menus', 'id')->whereNull('deleted_at'), // Assuming soft deletes
            ],
            'hair_concerns' => 'sometimes|string|max:3000',
            'image_paths' => [
                'sometimes',
                'array',
                'max:3',
            ],
            'image_paths.*' => [
                'file',
                'mimes:png,jpg,jpeg',
                'max:5120', // 5MB
            ],
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
            // Messages for 'area' and 'area_ids'
            'area.required' => 'The area field is required.',
            'area_ids.required' => 'The area selection is required.',
            'area_ids.array' => 'The area selection must be an array.',
            'area_ids.min' => 'At least one area must be selected.',
            'area_ids.exists' => 'The selected area is invalid.',
            // Messages for 'menu' and 'menu_ids'
            'menu.required' => 'The menu field is required.',
            'menu_ids.required' => 'The menu selection is required.',
            'menu_ids.array' => 'The menu selection must be an array.',
            'menu_ids.min' => 'At least one menu must be selected.',
            'menu_ids.exists' => 'The selected menu is invalid.',
            'hair_concerns.max' => 'The hair concerns may not be greater than 3000 characters.',
            'image_paths.array' => 'The image paths must be an array.',
            'image_paths.max' => 'No more than three images may be uploaded.',
            'image_paths.*.file' => 'Each image path must be a file.',
            'image_paths.*.mimes' => 'Each file must be of type: png, jpg, jpeg.',
            'image_paths.*.max' => 'Each file may not be greater than 5MB.',
        ];
    }
}
