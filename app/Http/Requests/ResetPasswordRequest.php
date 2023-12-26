<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\PasswordResetRequest;
use Illuminate\Support\Facades\Mail;
use App\Mail\PasswordResetSuccessMail;
use App\Notifications\PasswordResetSuccess;

class ResetPasswordRequest extends FormRequest
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
            'email' => [
                'required',
                'email',
                Rule::exists('users', 'email')
            ],
            'password' => [
                'required',
                'confirmed',
                // The minimum length is taken from the existing code (8 characters)
                'min:8',
                // The regex pattern is taken from the new code
                'regex:/^(?=.*[a-zA-Z])(?=.*\d).+$/',
                // The different rule is taken from the new code
                'different:email'
            ],
            'token' => [
                'required',
                Rule::exists('password_reset_requests', 'token')->where(function ($query) {
                    $query->where('expires_at', '>', now())->where('status', '!=', 'expired');
                }),
            ],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'email.required' => Lang::get('validation.required', ['attribute' => 'email']),
            'email.email' => Lang::get('validation.email', ['attribute' => 'email']),
            'email.exists' => Lang::get('passwords.user'),
            'password.required' => Lang::get('validation.required', ['attribute' => 'password']),
            // The minimum length error message is taken from the existing code (8 characters)
            'password.min' => Lang::get('validation.min.string', ['attribute' => 'password', 'min' => 8]),
            'password.regex' => Lang::get('validation.regex', ['attribute' => 'password']),
            'password.confirmed' => Lang::get('validation.confirmed', ['attribute' => 'password']),
            // The different rule error message is taken from the new code
            'password.different' => Lang::get('validation.different', ['attribute' => 'password']),
            'token.required' => Lang::get('validation.required', ['attribute' => 'token']),
            // The token exists error message is taken from the existing code
            'token.exists' => Lang::get('passwords.token_invalid'),
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($validator->errors()->isEmpty()) {
                // Always display a message indicating that a password reset email has been sent
                // to prevent guessing of registered email addresses.
                $validator->errors()->add('email', Lang::get('passwords.sent'));
            }
        });
    }

    /**
     * Execute the password reset process.
     *
     * @return string
     */
    public function resetPassword()
    {
        return DB::transaction(function () {
            $validatedData = $this->validated();
            $passwordResetRequest = PasswordResetRequest::where('token', $validatedData['token'])
                ->where('expires_at', '>', now())
                ->where('status', '!=', 'expired')
                ->firstOrFail();

            $user = User::where('email', $validatedData['email'])->firstOrFail();
            $user->password = Hash::make($validatedData['password']);
            $user->save();

            $passwordResetRequest->status = 'completed';
            $passwordResetRequest->save();

            // Send notification using the new method if it's available, otherwise use the old method.
            if (class_exists(PasswordResetSuccessMail::class)) {
                Mail::to($user->email)->send(new PasswordResetSuccessMail());
            } else {
                $user->notify(new PasswordResetSuccess());
            }

            return Lang::get('passwords.reset');
        });
    }
}
