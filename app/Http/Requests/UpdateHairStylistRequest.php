
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
        $user = Auth::user(); // No change here, just context for the next change
        if (!$user || !$this->requestBelongsToUser($user->id, $this->route('id'))) {
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
        $request = \App\Models\StylistRequest::find($requestId); // Updated to use StylistRequest model
        return $request && $request->user_id === $userId;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'request_id' => 'required|integer|exists:requests,id',
            'area' => 'required|string', // Updated to be required and a string
            'menu' => 'required|string', // Updated to be required and a string
            'hair_concerns' => 'nullable|string|max:3000',
            'image_paths' => 'nullable|array', // Updated to be nullable
            'image_paths.*' => 'nullable|file|mimes:png,jpg,jpeg|max:5120', // Updated to validate each file in the array
        ];

        if ($this->has('details')) {
            $rules['details'] = 'nullable|string';
        }
        if ($this->has('status')) {
            $rules['status'] = ['nullable', 'string', Rule::in(['pending', 'accepted', 'rejected'])];
        }

        return $rules;
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
            'area.required' => 'The area field is required.',
            'menu.required' => 'The menu field is required.',
            'hair_concerns.max' => 'The hair concerns may not be greater than 3000 characters.',
            'image_paths.array' => 'The image paths must be an array.',
            'image_paths.*.file' => 'Each image path must be a file.',
            'image_paths.*.mimes' => 'Each file must be of type: png, jpg, jpeg.',
            'image_paths.*.max' => 'Each file may not be greater than 5MB.',
            'details.string' => 'The details must be a string.',
            'status.string' => 'The status must be a string.',
            'status.in' => 'The selected status is invalid.',
        ];
    }
}
