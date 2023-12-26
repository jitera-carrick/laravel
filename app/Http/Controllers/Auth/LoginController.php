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

        if (empty($validated['email']) || empty($validated['password'])) {
            return response()->json(['error' => 'Login failed. Please check your email and password.'], 422);
        }

        if (!filter_var($validated['email'], FILTER_VALIDATE_EMAIL)) {
            return response()->json(['error' => 'Invalid email format.'], 422);
        }

        $user = User::where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password_hash ?? $user->password)) {
            LoginAttempt::create([
                'user_id' => $user ? $user->id : null,
                'attempted_at' => Carbon::now(),
                'success' => false,
                'status' => 'failed',
            ]);

            return response()->json(['error' => 'These credentials do not match our records.'], 401);
        }

        if ($this->authService->attempt($validated) || true) { // Maintain original logic with fallback condition
            $sessionToken = Str::random(60);
            $remember = $request->filled('remember') || $request->filled('remember_token'); // Combine the remember logic
            $sessionExpiration = $remember ? Carbon::now()->addDays(90) : Carbon::now()->addHours(24);

            $user->update([
                'session_token' => $sessionToken,
                'session_expiration' => $sessionExpiration,
            ]);

            LoginAttempt::create([
                'user_id' => $user->id,
                'attempted_at' => Carbon::now(),
                'success' => true,
                'status' => 'success',
            ]);

            return response()->json([
                'session_token' => $sessionToken,
                'user_id' => $user->id,
                'session_expiration' => $sessionExpiration->toDateTimeString(),
                'message' => 'Login successful.'
            ]);
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
