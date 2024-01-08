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
        return true; // Updated to always return true as per new code
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'email' => 'required|email',
            'token' => 'required|string|exists:password_resets,token', // Added 'exists' rule to check for token existence
            'password' => ['required', 'confirmed', Password::min(6)], // Updated to enforce a minimum length of 6 characters
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
            'email.required' => 'Please enter a valid email address.', // Updated error message as per requirement
            'email.email' => 'Please enter a valid email address.', // Updated error message as per requirement
            'token.required' => 'Invalid or expired reset token.', // Updated error message as per requirement
            'token.exists' => 'Invalid or expired reset token.', // Updated error message as per requirement
            'password.required' => 'Password must be at least 6 characters long.', // Updated error message as per requirement
            'password.confirmed' => 'The password confirmation does not match.',
            'password_confirmation.required_with' => 'The password confirmation is required when password is present.',
        ];
    }
}
