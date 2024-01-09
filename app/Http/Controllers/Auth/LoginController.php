<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\SessionRequest;
use App\Services\SessionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Events\FailedLogin;
use Illuminate\Support\Facades\Route;
use App\Http\Responses\ApiResponse; // Added line
use App\Models\LoginAttempt; // Added line

class LoginController extends Controller
{
    protected $sessionService;

    public function __construct(SessionService $sessionService)
    {
        $this->sessionService = $sessionService;
    }

    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
            'keep_session' => 'sometimes|boolean',
        ], [
            'email.required' => 'Email is required.',
            'email.email' => 'Invalid email format.',
            'password.required' => 'Password is required.',
            'keep_session.boolean' => 'Keep session must be a boolean.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first()
            ], 400);
        }

        $credentials = $request->only('email', 'password');
        $keepSession = $request->input('keep_session', false);

        try {
            $sessionData = $this->sessionService->login($credentials['email'], $credentials['password'], $keepSession);

            if ($sessionData) {
                return response()->json([
                    'status' => 200,
                    'message' => 'Login successful.',
                    'session_token' => $sessionData['token'],
                    'session_expiration' => $sessionData['expiration'],
                ], 200);
            } else {
                event(new FailedLogin($request->input('email'), now())); // Modified line
                return $this->handleLoginFailure($request->input('email')); // Modified line
            } 
        } catch (\Exception $e) {
            return response()->json(['message' => 'Internal Server Error'], 500);
        }
    }

    public function handleLoginFailure($email = null): JsonResponse // Modified line
    {
        if ($email) {
            // Trigger the FailedLogin event
            event(new FailedLogin($email, now())); // Modified line
            
            // Log the failed login attempt
            LoginAttempt::create([ // Added line
                'email' => $email, // Added line
                'attempted_at' => now(), // Added line
                'successful' => false, // Added line
            ]); // Added line

            // Return an error response
            return ApiResponse::loginFailure(); // Modified line
        } else {
            return response()->json([
                'status' => 200,
                'message' => 'Login failed. Please check your email and password and try again.'
            ], 200);
        }
    }

    public function cancelLogin(): JsonResponse
    {
        try {
            $this->sessionService->cancelOngoingLogin();
            return response()->json([
                'message' => 'Login process has been canceled successfully.'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to cancel login process.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // ... other methods ...
}

// Register the route for handling login failure
Route::get('/api/login/failure', [LoginController::class, 'handleLoginFailure']);
