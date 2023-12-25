<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\User;
use App\Models\PasswordResetRequest;
use App\Mail\PasswordResetMail; // Assuming this Mailable class exists

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    public function handlePasswordResetRequest(Request $request)
    {
        // Validate the email input
        $this->validate($request, [
            'email' => 'required|email',
        ]);

        try {
            // Find the user by email
            $user = User::where('email', $request->input('email'))->first();

            if (!$user) {
                return response()->json(['message' => 'User not found.'], 404);
            }

            // Generate a unique token
            $token = Str::random(60);

            // Store the token in the PasswordResetRequest model
            $passwordResetRequest = new PasswordResetRequest([
                'user_id' => $user->id,
                'token' => $token,
                'created_at' => Carbon::now(), // Use created_at instead of expires_at for storing the creation timestamp
                'expires_at' => Carbon::now()->addMinutes(60), // Set the expires_at field to 60 minutes from now
            ]);
            $passwordResetRequest->save();

            // Send the password reset email
            Mail::to($user->email)->send(new PasswordResetMail($token));

            // Return a response with a confirmation message
            return response()->json(['message' => 'Password reset email has been sent.'], 200);
        } catch (\Exception $e) {
            // Handle any exceptions that occur during the process
            return response()->json(['message' => 'An error occurred while processing your request.'], 500);
        }
    }
}
