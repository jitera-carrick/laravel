<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Assuming the user must be authenticated to update their profile
        return auth()->check();
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
                'sometimes',
                'required',
                'string',
                'email',
                Rule::unique('users', 'email')->ignore($this->user()->id),
            ],
            'password' => 'sometimes|required|string|min:8',
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
            'email.required' => 'The email address is required.',
            'email.email' => 'Invalid email format.',
            'email.unique' => 'Email already registered.',
            'password.required' => 'The password is required.',
            'password.min' => 'Password must be at least 8 characters long.',
        ];
    }
}
