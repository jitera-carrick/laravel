<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Any guest can attempt to login
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
            'email' => 'required|string|email|exists:users,email',
            'password' => 'required|string',
            'recaptcha' => 'required|string',
        ];
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param Validator $validator
     * @return void
     *
     * @throws HttpResponseException
     */
    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors();

        $customMessages = [];
        if ($errors->has('email')) {
            $customMessages['email'] = 'Invalid email address.';
        }
        if ($errors->has('password')) {
            $customMessages['password'] = 'Invalid password.';
        }
        if ($errors->has('recaptcha')) {
            $customMessages['recaptcha'] = 'Invalid recaptcha.';
        }

        throw new HttpResponseException(response()->json([
            'status' => 400,
            'errors' => $customMessages,
        ], 400));
    }
}
