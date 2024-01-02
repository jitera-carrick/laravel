<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateHairStylistRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Check if the user is logged in and the request_id belongs to the user
        $user = Auth::user();
        if (!$user || !$this->requestBelongsToUser($user->id, $this->request('request_id'))) {
            return false;
        }
        return true;
    }

    /**
     * Check if the request belongs to the user.
     *
     * @param int $userId
     * @param int $requestId
     * @return bool
     */
    protected function requestBelongsToUser($userId, $requestId)
    {
        $request = \App\Models\Request::find($requestId);
        return $request && $request->user_id === $userId;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'request_id' => 'required|integer|exists:requests,id',
            'area_ids' => 'required|array', // Updated to validate area_ids as an array
            'area_ids.*' => 'exists:areas,id', // Updated to ensure each ID exists in the areas table
            'menu_ids' => 'required|array', // Updated to validate menu_ids as an array
            'menu_ids.*' => 'exists:menus,id', // Updated to ensure each ID exists in the menus table
            'hair_concerns' => 'nullable|string|max:3000',
            'image_paths' => 'nullable|array|max:3', // Updated to limit the number of items in the array
            'image_paths.*' => 'nullable|file|mimes:png,jpg,jpeg|max:5120', // Updated to validate each file in the array
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
            'request_id.required' => 'The request ID is required.',
            'request_id.exists' => 'The selected request ID is invalid.',
            'area_ids.required' => 'The area IDs are required.', // Updated error message for area_ids
            'area_ids.array' => 'The area IDs must be an array.', // Updated error message for area_ids
            'area_ids.*.exists' => 'The selected area ID is invalid.', // Updated error message for area_ids
            'menu_ids.required' => 'The menu IDs are required.', // Updated error message for menu_ids
            'menu_ids.array' => 'The menu IDs must be an array.', // Updated error message for menu_ids
            'menu_ids.*.exists' => 'The selected menu ID is invalid.', // Updated error message for menu_ids
            'hair_concerns.max' => 'The hair concerns may not be greater than 3000 characters.',
            'image_paths.array' => 'The image paths must be an array.',
            'image_paths.max' => 'You may not upload more than 3 images.', // Updated error message for image_paths
            'image_paths.*.file' => 'Each image path must be a file.',
            'image_paths.*.mimes' => 'Each file must be of type: png, jpg, jpeg.',
            'image_paths.*.max' => 'Each file may not be greater than 5MB.',
        ];
    }
}
