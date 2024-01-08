<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'email' => 'required|email|unique:users,email',
            'password' => [
                'required',
                'confirmed',
                'min:8',
                'regex:/^(?=.*[a-zA-Z])(?=.*\d)(?=.*[^a-zA-Z\d]).+$/'
            ],
        ];
    }

    public function messages()
    {
        return [
            'email.required' => 'Invalid email address.',
            'email.email' => 'Invalid email address.',
            'email.unique' => 'This email address is already registered.',
            'password.required' => 'Invalid password. Password must be at least 8 characters long and include a number, a letter, and a special character.',
            'password.confirmed' => 'Passwords do not match.',
            'password.min' => 'Invalid password. Password must be at least 8 characters long and include a number, a letter, and a special character.',
            'password.regex' => 'Invalid password. Password must be at least 8 characters long and include a number, a letter, and a special character.',
        ];
    }
}
