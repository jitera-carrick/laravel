<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ValidateResetTokenRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'token' => 'required|string',
            'email' => 'required|email', // Added email validation as it's needed to check the token
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
            'token.required' => 'The reset token field is required.',
            'token.string' => 'The reset token must be a string.',
            'email.required' => 'The email field is required.', // Added custom message for email
            'email.email' => 'The email must be a valid email address.', // Added custom message for email
        ];
    }
}
