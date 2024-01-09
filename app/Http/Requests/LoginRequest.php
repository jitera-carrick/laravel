<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
            'password' => 'required|string|min:8', // Combined the password rules and added 'min:8' from the new code
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
}
