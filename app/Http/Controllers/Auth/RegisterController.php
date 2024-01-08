
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Requests\RegisterUserRequest;
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

    public function register(RegisterUserRequest $request)
    {
        // Validate that all required fields are provided and not empty.
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ], [
            // Validation messages are handled by RegisterUserRequest
            'email.required' => 'The email field is required.',
            'email.email' => 'Invalid email format.',
            'email.max' => 'The email may not be greater than 255 characters.',
            'email.unique' => 'Email already registered.',
            'password.required' => 'The password field is required.',
            'password.min' => 'Password must be at least 8 characters.',
            'password.confirmed' => 'The password confirmation does not match.',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator, response()->json($validator->errors(), 422));
        }

        // Create the user using UserService
        $user = $this->userService->createUser([
            'name' => $request->name,
            'email' => $request->email,
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
            'message' => 'User registered successfully. Please check your email to verify your account.',
        ], 201);
    }

    // Other methods...
}
