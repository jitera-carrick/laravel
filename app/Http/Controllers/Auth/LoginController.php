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
use Illuminate\Support\Facades\Validator;

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
                'remember' => 'sometimes|boolean', // Added remember validation from new code
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 400); // Changed error response to match new code
            }

            $validated = $validator->validated();
        }

        $credentials = $request->only('email', 'password');
        $remember = $request->filled('remember') || $request->filled('remember_token'); // Combine the remember logic

        $user = User::where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password_hash ?? $user->password)) { // Combined password check logic
            LoginAttempt::create([
                'user_id' => $user ? $user->id : null,
                'attempted_at' => Carbon::now(),
                'success' => false,
                'status' => 'failed', // Add status column to log the failed attempt
            ]);

            return response()->json(['error' => 'These credentials do not match our records.'], 401);
        }

        // Use AuthService if it's available and has an attempt method, otherwise proceed with the original logic
        if (method_exists($this->authService, 'attempt') && $this->authService->attempt($credentials, $remember)) {
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
                'status' => 200,
                'message' => 'Login successful.',
                'session_token' => $sessionToken,
                'session_expiration' => $sessionExpiration->toDateTimeString(), // Format the expiration date
                'user_id' => $user->id, // Added from existing code
            ], 200);
        } else {
            return response()->json(['error' => 'An unexpected error occurred.'], 500);
        }
    }

    public function cancelLogin()
    {
        // Existing method from old code...
    }

    public function maintainSession(Request $request)
    {
        // Existing method from old code...
    }

    // Other existing methods...
}
