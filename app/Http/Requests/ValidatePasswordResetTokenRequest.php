
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ValidatePasswordResetTokenRequest extends FormRequest
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
            'token.required' => 'The token field is required.',
            'token.string' => 'The token must be a string.',
        ];
    }
}
