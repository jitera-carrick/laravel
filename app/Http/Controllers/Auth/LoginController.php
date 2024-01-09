<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Services\SessionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Events\FailedLogin;
use Illuminate\Support\Facades\Route;
use App\Http\Resources\SessionResource;
use App\Http\Responses\ApiResponse;
use App\Services\AuthService;
use App\Models\LoginAttempt;

class LoginController extends Controller
{
    protected $sessionService;

    public function __construct(SessionService $sessionService)
    {
        $this->sessionService = $sessionService;
    }
    
    public function login(Request $request): JsonResponse
    {
        // Use LoginRequest if available, otherwise fallback to manual validation
        if ($request instanceof LoginRequest) {
            $credentials = $request->validated();
        } else {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required|min:8', // Updated minimum length requirement
                'keep_session' => 'sometimes|boolean',
            ], [
                'email.required' => 'Email is required.',
                'email.email' => 'Invalid email format.',
                'password.required' => 'Password is required.',
                'password.min' => 'Password must be at least 8 characters long.', // Updated error message
                'keep_session.boolean' => 'Keep session must be a boolean.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors()->first()
                ], 400);
            }

            $credentials = $request->only('email', 'password');
        }

        $keepSession = $request->input('keep_session', false);

        try {
            $sessionData = $this->sessionService->login($credentials['email'], $credentials['password'], $keepSession);

            if ($sessionData) {
                // Use SessionResource if available, otherwise fallback to manual response
                if (isset($sessionData['token'])) {
                    return response()->json([
                        'status' => 200,
                        'message' => 'Login successful.',
                        'session_token' => $sessionData['token'],
                        'session_expiration' => $sessionData['expiration'],
                    ], 200);
                } else {
                    return new SessionResource($sessionData);
                }
            } else {
                event(new FailedLogin($credentials['email']));
                return $this->handleLoginFailure($request); // Updated to pass the request object
            }
        } catch (\Exception $e) {
            return ApiResponse::errorResponse($e->getMessage());
        }
    }

    public function handleLoginFailure(Request $request): JsonResponse
    {
        $email = $request->input('email', null);
        if ($email) {
            // Trigger the FailedLogin event
            event(new FailedLogin($email, now()));
            
            // Log the failed login attempt
            LoginAttempt::create([
                'email' => $email,
                'attempted_at' => now(),
                'successful' => false,
            ]);

            // Return an error response
            return ApiResponse::loginFailure();
        } else {
            return response()->json([
                'status' => 401,
                'error' => 'Login failed. Please check your email and password.'
            ], 401);
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
            return ApiResponse::errorResponse($e->getMessage());
        }
    }

    // ... other methods ...
}

// Register the route for handling login failure
Route::match(['get', 'post'], '/api/login/failure', [LoginController::class, 'handleLoginFailure']);

// Register the route for canceling the login process
Route::post('/api/login/cancel', [LoginController::class, 'cancelLogin']);
