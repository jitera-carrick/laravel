
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
            'area' => 'required|string',
            'menu' => 'required|string',
            'hair_concerns' => 'required|string|max:3000',
            'image_paths' => 'array|max:3',
            'image_paths.*' => 'file|mimes:png,jpg,jpeg|max:5120', // 5MB
            'status' => 'required|in:pending,approved,rejected,cancelled',
            'user_id' => 'required|integer',
        ];
    }

    /**
     * Get the custom messages for validation errors.
     *
     * @return array
     */
    public function messages()
            'area.required' => 'The area field is required.',
            'area.string' => 'The area must be a string.',
            'menu.required' => 'The menu field is required.',
            'menu.string' => 'The menu must be a string.',
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
            'status.required' => 'The status is required.',
            'status.in' => 'The status must be one of the following types: pending, approved, rejected, cancelled.',
            'user_id.required' => 'The user ID is required.',
            'user_id.integer' => 'The user ID must be an integer.',
}
