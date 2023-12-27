<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\PasswordReset;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Mail\ResetPasswordConfirmationMail;

class ResetPasswordController extends Controller
{
    // Add your existing methods here

    // New method to handle password reset
    public function reset(Request $request, $token) // Updated method signature to include $token
    {
        // Existing code...

        // Update the user's password
        $user->password = Hash::make($password);
        $user->save();

        // Update the status in the "password_resets" table to indicate the password has been reset
        $passwordReset->status = 'completed';
        $passwordReset->save();

        // Delete the password reset token
        $passwordReset->delete();

        // Trigger a ResetPasswordNotification
        Notification::send($user, new ResetPasswordNotification()); // Use Notification facade as in the existing code

        // Return a success response
        return response()->json(['message' => 'Your password has been successfully reset.'], 200);
    }

    // New method to handle password reset based on the guideline
    public function resetPassword(Request $request)
    {
        // Validate the request
        $validatedData = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'new_password' => 'required|confirmed|min:6|regex:/^(?=.*[a-zA-Z])(?=.*\d)(?=.*[^a-zA-Z\d]).+$/',
            'password_confirmation' => 'required_with:new_password|same:new_password',
        ]);

        if ($validatedData->fails()) {
            return response()->json(['errors' => $validatedData->errors()], 422);
        }

        // Retrieve the user
        $user = User::findOrFail($request->input('user_id'));

        // Ensure the new password is different from the user's email address
        if (Str::contains($request->input('new_password'), $user->email)) {
            return response()->json(['message' => 'Password cannot be the same as the email address.'], 400);
        }

        // Hash the new password and update the user's password in the database
        $user->password = Hash::make($request->input('new_password'));
        $user->save();

        // Update the password_resets table to reflect that the password has been changed
        PasswordReset::where('email', $user->email)->update(['status' => 'completed']);

        // Send a confirmation email to the user's email address
        Mail::to($user->email)->send(new ResetPasswordConfirmationMail($user));

        // Return a JSON response with a success message
        return response()->json(['message' => 'Password updated successfully.'], 200);
    }

    // Existing methods...
}
