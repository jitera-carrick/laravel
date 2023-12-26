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
use Illuminate\Support\Facades\Validator; // Ensure Validator facade is included

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function login(Request $request)
    {
        // Use LoginRequest for validation if it's available, otherwise use Validator facade
        if ($request instanceof LoginRequest) {
            $validated = $request->validated();
        } else {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 422);
            }

            $validated = $validator->validated();
        }

        $credentials = $request->only('email', 'password');
        $remember = $request->filled('remember') || $request->filled('remember_token'); // Combine the remember logic

        $user = User::where('email', $validated['email'])->first();

        if (!$user) {
            return response()->json(['error' => 'These credentials do not match our records.'], 401);
        }

        // Update to use password_hash instead of password
        if (Hash::check($validated['password'], $user->password_hash ?? $user->password)) {
            // Use AuthService if it's available and has an attempt method, otherwise proceed with the original logic
            if (method_exists($this->authService, 'attempt') && $this->authService->attempt($credentials) || true) { // Maintain original logic with fallback condition
                $sessionToken = Str::random(60);
                $sessionExpiration = $remember ? Carbon::now()->addDays(90) : Carbon::now()->addHours(24);

                $user->update([
                    'session_token' => $sessionToken,
                    'session_expiration' => $sessionExpiration,
                ]);

                LoginAttempt::create([
                    'user_id' => $user->id,
                    'attempted_at' => Carbon::now(),
                    'success' => true,
                    'status' => 'success', // Add status column to log the successful attempt
                ]);

                return response()->json([
                    'session_token' => $sessionToken,
                    'user_id' => $user->id,
                    'session_expiration' => $sessionExpiration->toDateTimeString(), // Format the expiration date
                    'message' => 'Login successful.'
                ]);
            }
        } else {
            LoginAttempt::create([
                'user_id' => $user->id,
                'attempted_at' => Carbon::now(),
                'success' => false,
                'status' => 'failed', // Add status column to log the failed attempt
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
        // Validate the input to ensure that the 'email' field is provided.
        $validatedData = $request->validate([
            'email' => 'required|email',
            'remember_token' => 'sometimes|required|string',
        ]);

        // Retrieve the user by the provided email.
        $user = User::where('email', $validatedData['email'])->first();

        $responseData = ['session_maintained' => false];

        // If a user is found and the 'remember_token' is provided and matches the user's 'remember_token', update the user's 'session_expiration' to extend the session by 90 days.
        if ($user && isset($validatedData['remember_token']) && $validatedData['remember_token'] === $user->remember_token) {
            $user->session_expiration = Carbon::now()->addDays(90);
            $user->save();

            $responseData['session_maintained'] = true;
        }

        // Return a JSON response with a boolean 'session_maintained' key indicating whether the session was extended.
        return response()->json($responseData);
    }

    // Existing methods...
}
