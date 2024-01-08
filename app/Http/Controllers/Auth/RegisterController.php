<?php

namespace App\Http\Controllers\Auth;

use App\Http\Requests\RegisterRequest; // Corrected the request class name
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Services\UserService;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Notifications\VerifyEmailNotification;

class RegisterController extends Controller
{
    // Existing methods...
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function register(RegisterRequest $request) // Corrected the request class name
    {
        // Create the user using UserService
        $user = $this->userService->createUser([
            'name' => $request->name,
            'email' => $request->email,
            'username' => $request->username, // Added username to the user creation data
            'password' => Hash::make($request->password),
            'is_active' => false,
            // 'created_at' and 'updated_at' will be automatically set by Eloquent
        ]);

        // Send verification email using UserService
        $verificationToken = $this->userService->generateEmailVerificationToken($user);
        $user->notify(new VerifyEmailNotification($verificationToken));

        // Return a response with the confirmation message
        return response()->json([
            'status' => 'success',
            'message' => 'User registration successful.',
        ], 201);
    }

    // Other methods...
}
