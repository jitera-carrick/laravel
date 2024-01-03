<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Any guest can attempt to register
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
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
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
            'name.required' => 'The name field is required.',
            'name.string' => 'The name must be a string.',
            'name.max' => 'The name may not be greater than 255 characters.',
            'email.required' => 'The email field is required.',
            'email.string' => 'The email must be a string.',
            'email.email' => 'The email must be a valid email address.',
            'email.max' => 'The email may not be greater than 255 characters.',
            'email.unique' => 'The email has already been taken.',
            'password.required' => 'The password field is required.',
            'password.string' => 'The password must be a string.',
            'password.min' => 'The password must be at least 8 characters.',
            'password.confirmed' => 'The password confirmation does not match.',
        ];
    }

    /**
     * After the validation is passed, handle the registration process.
     *
     * @return array
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if (!$validator->errors()->isEmpty()) {
                return;
            }

            DB::transaction(function () {
                $user = new \App\Models\User();
                $user->name = $this->input('name');
                $user->email = $this->input('email');
                $user->password = Hash::make($this->input('password'));
                $user->save();

                $verificationToken = \Str::random(60);
                DB::table('email_verification_tokens')->insert([
                    'user_id' => $user->id,
                    'token' => $verificationToken,
                    'created_at' => now(),
                ]);

                // Send confirmation email (pseudo code)
                // Mail::to($user->email)->send(new EmailVerification($verificationToken));
            });
        });
    }
}
