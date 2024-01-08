
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
        // Assuming RequestModel exists and has a relationship with User
        // Replace RequestModel with the actual model name
        $request = \App\Models\Request::find($requestId); // Updated to use the correct Request model
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
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($this->user()->id),
            ],
            'password' => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required_with:password',
            'request_id' => 'required|integer|exists:requests,id',
            'area' => 'required|string',
            'menu' => 'required|string',
            'hair_concerns' => 'nullable|string|max:3000',
            'image_paths' => 'nullable|array',
            'image_paths.*' => 'nullable|file|mimes:png,jpg,jpeg|max:5120',
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
            'password.min' => 'The password must be at least 8 characters.',
            'password.confirmed' => 'The password confirmation does not match.',
            'password_confirmation.required_with' => 'The password confirmation is required when password is present.',
            'request_id.required' => 'The request ID is required.',
            'request_id.exists' => 'The selected request ID is invalid.',
            'area.required' => 'The area field is required.',
            'menu.required' => 'The menu field is required.',
            'hair_concerns.max' => 'The hair concerns may not be greater than 3000 characters.',
            'image_paths.array' => 'The image paths must be an array.',
            'image_paths.*.file' => 'Each image path must be a file.',
            // ... other existing messages
            'image_paths.*.mimes' => 'Each file must be of type: png, jpg, jpeg.',
            'image_paths.*.max' => 'Each file may not be greater than 5MB.',
        ];
    }
}
