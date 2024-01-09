
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'email' => 'required|email',
            'token' => 'required',
            'password' => 'required|confirmed|min:8',
            'password_confirmation' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'email.required' => 'An email address is required.',
            'email.email' => 'You must provide a valid email address.',
            'token.required' => 'A token is required.',
            'password.required' => 'A password is required.',
            'password.confirmed' => 'Passwords do not match.',
            'password.min' => 'Password must be at least 8 characters.',
            'password_confirmation.required' => 'Password confirmation is required.',
        ];
    }
}
