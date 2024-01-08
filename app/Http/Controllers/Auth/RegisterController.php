<?php

namespace App\Http\Controllers\Auth;

use App\Http\Requests\RegisterUserRequest; // Use the correct FormRequest class
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator; // Import the Validator facade
use Illuminate\Validation\ValidationException;
use App\Notifications\VerifyEmailNotification;
use App\Services\AuthService; // Assuming AuthService exists and is correctly implemented
use App\Http\Resources\SuccessResource;
use App\Http\Resources\ErrorResource;

class RegisterController extends Controller
{
    // Existing methods...

    public function register(RegisterUserRequest $request) // Use the correct FormRequest class
    {
        // Validate that all required fields are provided and not empty.
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
        ], [
            'email.required' => 'The email field is required.',
            'email.email' => 'Invalid email format.',
            'email.max' => 'The email may not be greater than 255 characters.',
            'email.unique' => 'Email already registered.',
            'password.required' => 'The password field is required.',
            'password.min' => 'Password must be at least 8 characters long.',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            $statusCode = $errors->has('email') && $errors->get('email')[0] === 'Email already registered.' ? 409 : 422;
            throw new ValidationException($validator, response()->json($validator->errors(), $statusCode));
        }

        try {
            // Assuming AuthService has a createUser method that handles user creation
            $user = (new AuthService())->createUser([
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);
            $verificationToken = $user->generateEmailConfirmationToken();
            $user->notify(new VerifyEmailNotification($verificationToken));

            return (new SuccessResource(['status' => 201, 'message' => 'User registered successfully.']))->response()->setStatusCode(201);
        } catch (\Exception $e) {
            $statusCode = $e->getCode() === 0 ? 500 : $e->getCode();
            return (new ErrorResource(['message' => $e->getMessage(), 'status_code' => $statusCode]))->response()->setStatusCode($statusCode);
        }
    }

    // Other methods...
}
