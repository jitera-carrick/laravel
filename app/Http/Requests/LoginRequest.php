
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
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:8',
            'keep_session' => 'sometimes|boolean',
        ];

        return $rules;
    }

    /**
     * Get the custom messages for validation errors.
     *
     * @return array
     */
    public function messages()
    {
        return ['keep_session.boolean' => 'The keep session field must be true or false.'];
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
     * Customize the failed validation response.
     *
     * @param Validator $validator
     * @return void
     *
     * @throws HttpResponseException
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'status' => 422,
            'errors' => $validator->errors(),
        ], 422));
    }
}
