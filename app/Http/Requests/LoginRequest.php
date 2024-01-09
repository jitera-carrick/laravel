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
            'password' => 'required|string|min:8',
            'keep_session' => 'sometimes|boolean',
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
        if (isset($input['keep_session'])) {
            $input['keep_session'] = filter_var($input['keep_session'], FILTER_VALIDATE_BOOLEAN);
        }
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

        $customMessages = [
            'email.email' => 'Invalid email format.',
            'password.min' => 'Password must be at least 8 characters long.',
            'keep_session.boolean' => 'Keep Session must be a boolean.',
        ];

        $transformedErrors = [];

        foreach ($errors->getMessages() as $field => $message) {
            $transformedErrors[$field] = $customMessages[$message[0]] ?? $message[0];
        }

        throw new HttpResponseException(response()->json([
            'status' => 422,
            'errors' => $transformedErrors,
        ], 422));
    }
}
