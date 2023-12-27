<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\PasswordResetToken;
use App\Models\PasswordPolicy;
use App\Notifications\ResetPasswordNotification;

class ResetPasswordController extends Controller
{
    // Add your existing methods here

    // New method to handle password reset
    public function reset(Request $request)
    {
        // Retrieve password policy
        $passwordPolicy = PasswordPolicy::firstOrFail();

        // Validation rules
        $rules = [
            'email' => 'required|email|exists:password_reset_tokens,email',
            'password' => [
                'required',
                'confirmed',
                'min:' . $passwordPolicy->minimum_length,
                $passwordPolicy->require_digits ? 'regex:/[0-9]/' : '',
                $passwordPolicy->require_letters ? 'regex:/[a-zA-Z]/' : '',
                $passwordPolicy->require_special_characters ? 'regex:/[\W]/' : '',
            ],
        ];

        // Custom error messages
        $messages = [
            'password.regex' => 'The password must include the required types of characters.',
        ];

        // Validate request
        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Retrieve the user by their email address
        $user = User::where('email', $request->email)->firstOrFail();

        // Encrypt the new password and update the user record
        $user->password = Hash::make($request->password);
        $user->save();

        // Delete the password reset token
        PasswordResetToken::where('email', $request->email)->delete();

        // Trigger a ResetPasswordNotification
        $user->notify(new ResetPasswordNotification());

        // Return a success response
        return response()->json(['message' => 'Password has been successfully reset.']);
    }
}
