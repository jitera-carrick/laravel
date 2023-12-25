<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Lang;

class ResetPasswordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Assuming the old code has an authorize method
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
            // Assuming the old code has other rules
            'email' => [
                'required',
                'email',
                Rule::exists('users', 'email')
            ],
            'password' => [
                'required',
                'min:8',
                'confirmed'
            ],
            'token' => [
                'required',
                Rule::exists('password_reset_requests', 'token')->where(function ($query) {
                    $query->where('expires_at', '>', now())->where('status', '!=', 'expired');
                }),
            ],
            // Add other rules here if needed
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'email.required' => Lang::get('validation.required', ['attribute' => 'email']),
            'email.email' => Lang::get('validation.email', ['attribute' => 'email']),
            'email.exists' => Lang::get('passwords.user'),
            'password.required' => Lang::get('validation.required', ['attribute' => 'password']),
            'password.min' => Lang::get('validation.min.string', ['attribute' => 'password', 'min' => 8]),
            'password.confirmed' => Lang::get('validation.confirmed', ['attribute' => 'password']),
            'token.required' => Lang::get('validation.required', ['attribute' => 'token']),
            'token.exists' => Lang::get('passwords.token'),
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if (!$validator->errors()->isEmpty()) {
                // Always display a message indicating that a password reset email has been sent
                // to prevent guessing of registered email addresses.
                $validator->errors()->add('email', Lang::get('passwords.sent'));
            }
        });
    }
}
