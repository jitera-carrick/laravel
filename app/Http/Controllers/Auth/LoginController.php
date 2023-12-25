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

        $credentials = $request->only('email', 'password');
        $remember = $request->filled('remember_token');

        if (!filter_var($credentials['email'], FILTER_VALIDATE_EMAIL)) {
            return response()->json(['message' => 'Invalid email format.'], 422);
        }

        $user = User::where('email', $credentials['email'])->first();

        if ($user && Hash::check($credentials['password'], $user->password)) {
            if ($this->authService->attempt($credentials)) {
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
                ]);

                return response()->json([
                    'session_token' => $sessionToken,
                    'session_expiration' => $sessionExpiration,
                ]);
            }
        }

        LoginAttempt::create([
            'user_id' => $user ? $user->id : null,
            'attempted_at' => Carbon::now(),
            'success' => false,
        ]);

        return response()->json([
            'message' => 'These credentials do not match our records.'
        ], 401);
    }

    public function cancelLogin()
    {
        if (Auth::check()) {
            Auth::logout();
        }

        return response()->json(['message' => 'Login process has been canceled successfully.', 'login_canceled' => true], 200);
    }

    // Existing methods...
}
