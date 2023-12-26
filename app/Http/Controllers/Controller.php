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
        $validatedData = $request->validate([
            'email' => 'required|email',
        ]);

        try {
            // Find the user by email
            $user = User::where('email', $validatedData['email'])->first();

            if (!$user) {
                return response()->json(['message' => 'User not found.'], 404);
            }

            // Generate a unique token
            $token = Str::random(60);

            // Store the token in the PasswordResetRequest model
            $passwordResetRequest = new PasswordResetRequest([
                'user_id' => $user->id,
                'token' => $token,
                'created_at' => Carbon::now(),
                'expires_at' => Carbon::now()->addMinutes(60),
            ]);
            $passwordResetRequest->save();

            // Send the password reset email
            Mail::to($user->email)->send(new PasswordResetMail($token, $user)); // Assuming the PasswordResetMail accepts a user object

            // Return a JSON response with a `reset_requested` boolean key
            return response()->json(['reset_requested' => true], 200);
        } catch (\Exception $e) {
            // Handle any exceptions that occur during the process
            return response()->json(['message' => 'An error occurred while processing your request.', 'error' => $e->getMessage()], 500);
        }
    }
}
