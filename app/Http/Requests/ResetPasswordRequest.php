<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class ResetPasswordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Assuming any authenticated user can reset their password
        return !is_null($this->user());
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'email' => 'required|email|exists:users,email',
            'token' => 'required|string|exists:password_resets,token|max:255',
            'password' => [
                'required',
                'confirmed',
                Password::min(8)->mixedCase()->numbers()->symbols(),
            ],
            'password_confirmation' => 'required_with:password',
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
            'email.required' => 'Invalid email address.',
            'email.email' => 'Invalid email address.',
            'email.exists' => 'The email does not exist in our records.',
            'token.required' => 'Invalid or expired token.',
            'token.exists' => 'Invalid or expired token.',
            'token.max' => 'Invalid or expired token.',
            'password.required' => 'Invalid password. Password must be at least 8 characters long and include a number, a letter, and a special character.',
            'password.confirmed' => 'The password confirmation does not match.',
            'password_confirmation.required_with' => 'The password confirmation is required when password is present.',
        ];
    }
}
