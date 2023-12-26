<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Models\User;
use App\Notifications\VerifyEmail;

class RegisterController extends Controller
{
    // Add the new register method below

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Check if the email provided matches a valid email format using a regular expression.
        if (!filter_var($request->email, FILTER_VALIDATE_EMAIL)) {
            return response()->json(['message' => 'Invalid email format.'], 400);
        }

        $user = User::where('email', $request->email)->first();

        if ($user) {
            return response()->json(['message' => 'The email address is already registered.'], 409);
        }

        $user = new User;
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->email_verified_at = null;
        $user->save();

        $verificationToken = Str::random(60);
        // Here you would save the verification token with the user record or send it via email
        // For example, you could use a notification like this:
        // $user->notify(new VerifyEmail($verificationToken));

        // Alternatively, you could send the email directly using the Mail facade
        Mail::send('emails.verify_email', ['token' => $verificationToken], function ($message) use ($user) {
            $message->to($user->email);
            $message->subject('Email Verification');
        });

        return response()->json([
            'user_id' => $user->id,
            'message' => 'User registered successfully. Please check your email to verify your account.',
        ]);
    }

    // ... rest of the RegisterController code ...
}
