<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\AuthService;
use App\Models\LoginAttempt;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\LoginRequest;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function login(LoginRequest $request)
    {
        $validated = $request->validated();

        // Ensure that the input email and password are validated using the `LoginRequest` class.
        // Use the `filter_var` function to validate the email format.
        if (!filter_var($validated['email'], FILTER_VALIDATE_EMAIL)) {
            return response()->json(['error' => 'Invalid email format.'], 422);
        }

        // Check if email and password are not empty
        if (empty($validated['email']) || empty($validated['password'])) {
            return response()->json(['error' => 'Login failed. Please check your email and password.'], 422);
        }

        // Retrieve the user from the database using the `User` model with the provided email.
        $user = User::where('email', $validated['email'])->first();

        // Use the `Hash` facade to check if the provided password matches the `password_hash` in the database.
        if ($user && Hash::check($validated['password'], $user->password_hash ?? $user->password)) {
            // Determine if the "Keep Session" option is selected by checking the presence of the `remember_token` in the request.
            $remember = $request->filled('remember') || $request->filled('remember_token');

            // Generate a `session_token` using the `Str::random` method.
            $sessionToken = Str::random(60);

            // Set the `session_expiration` to either 90 days or 24 hours from the current time based on the "Keep Session" option.
            $sessionExpiration = $remember ? Carbon::now()->addDays(90) : Carbon::now()->addHours(24);

            // Update the user's `session_token` and `session_expiration` in the database.
            $user->update([
                'session_token' => $sessionToken,
                'session_expiration' => $sessionExpiration,
            ]);

            // Log the successful login attempt using the `LoginAttempt` model.
            LoginAttempt::create([
                'user_id' => $user->id,
                'attempted_at' => Carbon::now(),
                'success' => true,
                'status' => 'success',
            ]);

            // Return a JSON response with the `session_token` and `session_expiration` formatted to a datetime string.
            return response()->json([
                'session_token' => $sessionToken,
                'user_id' => $user->id,
                'session_expiration' => $sessionExpiration->toDateTimeString(),
                'message' => 'Login successful.'
            ]);
        } else {
            // Log the failed login attempt using the `LoginAttempt` model.
            LoginAttempt::create([
                'user_id' => $user ? $user->id : null,
                'attempted_at' => Carbon::now(),
                'success' => false,
                'status' => 'failed',
            ]);

            return response()->json([
                'error' => 'These credentials do not match our records.'
            ], 401);
        }
    }

    public function cancelLogin()
    {
        if (Auth::check()) {
            Auth::logout();
        }

        return response()->json(['message' => 'Login process has been canceled successfully.', 'login_canceled' => true], 200);
    }

    public function maintainSession(Request $request)
    {
        $validatedData = $request->validate([
            'email' => 'required|email',
            'remember_token' => 'sometimes|required|string',
        ]);

        $user = User::where('email', $validatedData['email'])->first();

        $responseData = ['session_maintained' => false];

        if ($user && isset($validatedData['remember_token']) && $validatedData['remember_token'] === $user->remember_token) {
            $user->session_expiration = Carbon::now()->addDays(90);
            $user->save();

            $responseData['session_maintained'] = true;
        }

        return response()->json($responseData);
    }

    // Existing methods...
}
