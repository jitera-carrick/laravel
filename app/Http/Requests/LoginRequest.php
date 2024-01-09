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
        return true; // No specific authorization logic for login
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'email' => 'required|string|email',
            'password' => 'required|string|min:8', // Password validation rule updated
            'keep_session' => 'boolean',
        ];
        return $rules;
    }

    /**
     * Get the sanitized input data from the request.
     *
     * @return array
     */
    public function validated()
    {
        $input = parent::validated();
        $input['keep_session'] = filter_var($input['keep_session'], FILTER_VALIDATE_BOOLEAN);
        return $input;
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
            $customMessages['email'] = "Invalid email format.";
        }
        if ($errors->has('password')) {
            $customMessages['password'] = "Password must be at least 8 characters long.";
        }
        if ($errors->has('keep_session')) {
            $customMessages['keep_session'] = "Keep session must be a boolean.";
        }

        $response = response()->json([
            'status' => 400,
            'message' => 'Bad Request',
            'errors' => $customMessages,
        ], 400);

        throw new HttpResponseException($response);
    }
}
