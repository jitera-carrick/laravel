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
use Exception;

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
                'email' => 'required|email|exists:users,email', // Combined validation rules
                'password' => 'required',
                'remember' => 'sometimes|boolean', // Added remember validation from new code
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422); // Changed error response to match new code and use first error
            }

            $validated = $validator->validated();
        }

        $credentials = $request->only('email', 'password');
        $remember = $request->filled('remember') || $request->filled('remember_token'); // Combine the remember logic

        $user = User::where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) { // Combined password check logic
            LoginAttempt::create([
                'user_id' => $user ? $user->id : null,
                'attempted_at' => Carbon::now(),
                'success' => false,
                'status' => 'failed', // Add status column to log the failed attempt
            ]);

            return response()->json(['error' => 'These credentials do not match our records.'], 401);
        }

        try {
            // Authenticate the user using AuthService
            $authData = $this->authService->authenticate($validated['email'], $validated['password']);

            // Return success response with user session information
            return response()->json([
                'status' => 200,
                'message' => 'Login successful.',
                'user' => [
                    'id' => $authData['user']->id,
                    'email' => $authData['user']->email,
                    'session_token' => $authData['session_token'],
                    'session_expiration' => $authData['session_expiration']->toDateTimeString(), // Format the expiration date
                ]
            ], 200);
        } catch (Exception $e) {
            // Handle authentication failure
            if ($e->getMessage() === 'Authentication failed.') {
                return response()->json(['error' => 'Invalid credentials'], 401);
            }

            // Handle other exceptions
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function cancelLogin()
    {
        // Since no backend action is required, we directly return a success response.
        return response()->json([
            'status' => 200,
            'message' => 'Login cancelled successfully.'
        ], 200);
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
        if ($user && isset($validatedData['remember_token']) && $validatedData['remember_token'] === $user's remember_token) {
            $user->session_expiration = Carbon::now()->addDays(90);
            $user->save();

            $responseData['session_maintained'] = true;
        }

        // Return a JSON response with a boolean 'session_maintained' key indicating whether the session was extended.
        return response()->json($responseData);
    }

    // Other existing methods...
}
