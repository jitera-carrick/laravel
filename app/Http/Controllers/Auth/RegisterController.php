<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;
use App\Http\Requests\RegisterRequest;
use Illuminate\Support\Facades\Queue;
use App\Mail\EmailVerification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    // ... (other methods)

    public function register(RegisterRequest $request)
    {
        // Validate the request with custom error messages
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'name.required' => 'The name is required.',
            'email.required' => 'The email field is required.',
            'email.email' => 'Invalid email format.',
            'email.unique' => 'Email already registered.',
            'password.required' => 'The password field is required.',
            'password.min' => 'Password must be at least 8 characters.',
            'password.confirmed' => 'The password confirmation does not match.',
        ]);

        if ($validator->fails()) {
            return Response::json([
                'status' => 'error',
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $validatedData = $validator->validated();

        try {
            $user = User::create([
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'password' => Hash::make($validatedData['password']),
                'remember_token' => Str::random(60),
                // 'created_at' and 'updated_at' will be set automatically by Eloquent
            ]);

            // Dispatch an email verification job
            Queue::push(function ($job) use ($user) {
                Mail::to($user->email)->send(new EmailVerification($user));
                $job->delete();
            });

            return Response::json([
                'status' => 201,
                'message' => 'User registered successfully.',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'created_at' => $user->created_at->toIso8601String(),
                ]
            ], 201);
        } catch (\Exception $e) {
            return Response::json([
                'status' => 'error',
                'message' => 'Server error',
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    // ... (other methods)
}
