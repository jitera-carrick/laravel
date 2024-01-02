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
            'area_ids' => 'required|array|min:1', // Updated to ensure the array is not empty
            'area_ids.*' => 'required|integer|exists:areas,id', // Updated to validate each area_id
            'menu_ids' => 'required|array|min:1', // Updated to ensure the array is not empty
            'menu_ids.*' => 'required|integer|exists:menus,id', // Updated to validate each menu_id
            'hair_concerns' => 'nullable|string|max:3000',
            'image_paths' => 'nullable|array|max:3', // Updated to limit the number of files
            'image_paths.*' => 'nullable|file|mimes:png,jpg,jpeg|max:5120', // No change needed here
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
            'area_ids.required' => 'The area IDs are required.',
            'area_ids.array' => 'The area IDs must be an array.',
            'area_ids.min' => 'The area IDs cannot be empty.',
            'area_ids.*.required' => 'Each area ID is required.',
            'area_ids.*.integer' => 'Each area ID must be an integer.',
            'area_ids.*.exists' => 'The selected area ID is invalid.',
            'menu_ids.required' => 'The menu IDs are required.',
            'menu_ids.array' => 'The menu IDs must be an array.',
            'menu_ids.min' => 'The menu IDs cannot be empty.',
            'menu_ids.*.required' => 'Each menu ID is required.',
            'menu_ids.*.integer' => 'Each menu ID must be an integer.',
            'menu_ids.*.exists' => 'The selected menu ID is invalid.',
            'hair_concerns.max' => 'The hair concerns may not be greater than 3000 characters.',
            'image_paths.array' => 'The image paths must be an array.',
            'image_paths.max' => 'You may not upload more than 3 images.',
            'image_paths.*.file' => 'Each image path must be a file.',
            'image_paths.*.mimes' => 'Each file must be of type: png, jpg, jpeg.',
            'image_paths.*.max' => 'Each file may not be greater than 5MB.',
        ];
    }
}
