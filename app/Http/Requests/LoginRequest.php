
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
        return [
            'email' => 'required|string|email',
            'password' => 'required|string',
            'keep_session' => 'boolean',
        ];
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
