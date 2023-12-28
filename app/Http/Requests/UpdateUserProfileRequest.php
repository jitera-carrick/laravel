<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UpdateUserProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Implement additional authorization logic if required
        // For now, we allow all authenticated users to update their profile
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
            'id' => 'required|integer|exists:users,id',
            'name' => 'sometimes|string|max:255',
            'email' => [
                'sometimes',
                'required',
                'email',
                Rule::unique('users')->ignore($this->id),
            ],
            'password' => 'sometimes|required_with:password_confirmation|confirmed|min:6',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'id.required' => 'The user ID is required.',
            'id.exists' => 'The user ID does not exist.',
            'name.string' => 'The name must be a string.',
            'name.max' => 'The name may not be greater than 255 characters.',
            'email.required' => 'The email address is required.',
            'email.email' => 'The email address must be a valid email.',
            'email.unique' => 'The email address is already in use by another account.',
            'password.required_with' => 'The password is required when a password confirmation is provided.',
            'password.confirmed' => 'The password confirmation does not match.',
            'password.min' => 'The password must be at least 6 characters.',
        ];
    }

    /**
     * Handle a passed validation.
     *
     * @return void
     */
    protected function passedValidation()
    {
        if ($this->filled('password')) {
            $this->merge([
                'password_hash' => Hash::make($this->password),
                'last_password_reset' => now(),
            ]);
        }
    }

    /**
     * Perform any additional validation after the main rules have been applied.
     *
     * @return void
     */
    protected function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->filled('password')) {
                // Check if the password is different from the old one
                $user = DB::table('users')->where('id', $this->id)->first();
                if (Hash::check($this->password, $user->password_hash)) {
                    $validator->errors()->add('password', 'The new password must be different from the current password.');
                }
            }
        });
    }
}
