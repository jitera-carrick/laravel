
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Assuming the user must be authenticated to update their profile
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'user_id' => 'required|integer',
            'email' => 'required|string|email',
            'password_hash' => 'required|string|confirmed',
            'password_confirmation' => 'required|string',
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
            'user_id.required' => 'The user ID is required.',
            'user_id.integer' => 'The user ID must be an integer.',
            'email.required' => 'The email address is required.',
            'email.email' => 'The email address must be a valid email format.',
            'password_hash.required' => 'The password is required.',
            'password_hash.confirmed' => 'The password confirmation does not match.',
            'password_confirmation.required' => 'The password confirmation is required.',
        ];
    }
}
