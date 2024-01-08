
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Requests\RegisterUserRequest; // Updated import
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Notifications\VerifyEmailNotification;

class RegisterController extends Controller
{
    // Existing methods...

    public function register(RegisterUserRequest $request) // Updated parameter type
    {
        // Since we're using a form request (RegisterUserRequest), we don't need the manual validation here.
        // The form request will automatically handle it and throw an exception if validation fails.

        // Create the user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'remember_token' => Str::random(10), // Adjusted to match the guideline
            // 'created_at' and 'updated_at' will be automatically set by Eloquent
        ]);

        // Send verification email
        $user->notify(new VerifyEmailNotification($user->remember_token));

        // Return a response with the user ID
        return response()->json([
            'status' => 201,
            'message' => 'User registered successfully. Please check your email to verify.',
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
