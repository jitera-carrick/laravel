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
            // Combining the 'area' and 'area_ids' fields
            'area' => 'required_without:area_ids|string',
            'area_ids' => 'required_without:area|array|min:1',
            'area_ids.*' => 'integer|exists:areas,id',
            // Combining the 'menu' and 'menu_ids' fields
            'menu' => 'required_without:menu_ids|string',
            'menu_ids' => 'required_without:menu|array|min:1',
            'menu_ids.*' => 'integer|exists:menus,id',
            // Keeping the 'hair_concerns' validation from the new code as it is more restrictive
            'hair_concerns' => 'required|string|max:3000',
            // Keeping the 'image_paths' validation from the new code as it is more restrictive
            'image_paths' => 'required|array|max:3',
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
            // Messages for 'area' and 'area_ids'
            'area.required_without' => 'The area field is required when area ids are not present.',
            'area_ids.required_without' => 'The area ids field is required when area is not present.',
            'area_ids.array' => 'The area ids must be an array.',
            'area_ids.min' => 'At least one area id is required.',
            'area_ids.*.integer' => 'Each area id must be an integer.',
            'area_ids.*.exists' => 'The selected area id is invalid.',
            // Messages for 'menu' and 'menu_ids'
            'menu.required_without' => 'The menu field is required when menu ids are not present.',
            'menu_ids.required_without' => 'The menu ids field is required when menu is not present.',
            'menu_ids.array' => 'The menu ids must be an array.',
            'menu_ids.min' => 'At least one menu id is required.',
            'menu_ids.*.integer' => 'Each menu id must be an integer.',
            'menu_ids.*.exists' => 'The selected menu id is invalid.',
            // Messages for 'hair_concerns'
            'hair_concerns.required' => 'The hair concerns field is required.',
            'hair_concerns.max' => 'The hair concerns may not be greater than 3000 characters.',
            // Messages for 'image_paths'
            'image_paths.required' => 'The image paths field is required.',
            'image_paths.array' => 'The image paths must be an array.',
            'image_paths.max' => 'The image paths may not have more than 3 items.',
            'image_paths.*.file' => 'Each image path must be a file.',
            'image_paths.*.mimes' => 'Each file must be of type: png, jpg, jpeg.',
            'image_paths.*.max' => 'Each file may not be greater than 5MB.',
        ];
    }
}
