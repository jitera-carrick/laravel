
<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Http\Requests\RegisterRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Mail;
use App\Notifications\VerifyEmailNotification;
use App\Models\EmailVerificationToken;

class RegisterController extends Controller
{
    // Existing methods...

    public function register(RegisterRequest $request)
    {
        // Validation is handled by RegisterRequest, no need for manual validation here.

        // Create the user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'remember_token' => Str::random(60),
            // 'created_at' and 'updated_at' will be automatically set by Eloquent
        ]);

        // Generate email verification token
        $token = Str::random(60);
        EmailVerificationToken::create([
            'token' => $token,
            'user_id' => $user->id,
            'expires_at' => now()->addHours(24),
            'used' => false
        ]);

        // Send verification email
        $verificationUrl = route('verification.verify', ['token' => $token]);
        Mail::send('emails.verify', ['url' => $verificationUrl], function ($message) use ($user) {
            $message->to($user->email)->subject('Verify Email Address');
        });

        // Return a response with the user ID
        return response()->json([
            'status' => 201,
            'message' => 'User registered successfully.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'created_at' => $user->created_at->toIso8601String(),
            ]
        ], 201);
    }

    // Other methods...
}
