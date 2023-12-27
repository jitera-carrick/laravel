<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePasswordPolicyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Check if the user has the 'update_password_policy' permission
        return $this->user()->can('update_password_policy');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'minimum_length' => 'required|integer|min:1',
            'require_digits' => 'required|boolean',
            'require_letters' => 'required|boolean',
            'require_special_characters' => 'required|boolean',
        ];
    }

    /**
     * Get custom messages for validation errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'minimum_length.required' => 'The minimum length is required.',
            'minimum_length.integer' => 'The minimum length must be an integer.',
            'minimum_length.min' => 'The minimum length must be at least 1.',
            'require_digits.required' => 'The digits requirement is required.',
            'require_digits.boolean' => 'The digits requirement must be a boolean.',
            'require_letters.required' => 'The letters requirement is required.',
            'require_letters.boolean' => 'The letters requirement must be a boolean.',
            'require_special_characters.required' => 'The special characters requirement is required.',
            'require_special_characters.boolean' => 'The special characters requirement must be a boolean.',
        ];
    }
}
