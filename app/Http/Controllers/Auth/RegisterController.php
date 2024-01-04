
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Http\Requests\RegisterRequest;
use App\Models\EmailVerificationToken;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Support\Facades\DB;
use App\Jobs\SendEmailJob;

class RegisterController extends Controller
{
    // Existing methods...

    public function register(RegisterRequest $request)
    {
        // Check if the email or username already exists
        $existingUser = User::where('email', $request->email)
                            ->orWhere('username', $request->username)
                            ->first();

        if ($existingUser) {
            return response()->json([
                'status' => 'error',
                'message' => 'The email or username is already in use.'
            ], 409);
        }

        // Create the user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'username' => $request->username,
            'password_hash' => Hash::make($request->password), // Assuming password_hash is the correct field
            'remember_token' => Str::random(60),
            // 'created_at' and 'updated_at' will be automatically set by Eloquent
        ]);

        // Generate email verification token
        $emailVerificationToken = EmailVerificationToken::create([
            'token' => Str::random(60),
            'expires_at' => now()->addHours(24),
            'used' => false,
            'user_id' => $user->id
        ]);

        // Dispatch job to send verification email
        dispatch(new SendEmailJob($user, $emailVerificationToken));

        // Return a response with the user ID
        return response()->json([
            'status' => 'success',
            'message' => 'User registered successfully. Please check your email to verify your account.',
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
