<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\SessionRequest;
use App\Http\Requests\LoginRequest;
use App\Services\SessionService;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Events\FailedLogin;
use Illuminate\Support\Facades\Route;
use App\Helpers\TokenHelper;
use App\Http\Responses\ApiResponse;

class LoginController extends Controller
{
    protected $sessionService;
    protected $authService;

    public function __construct(SessionService $sessionService, AuthService $authService = null)
    {
        $this->sessionService = $sessionService;
        $this->authService = $authService ?? new AuthService();
    }

    public function login(Request $request): JsonResponse
    {
        // Determine if the request is using the new or old validation
        if ($request instanceof LoginRequest) {
            // Old code path
            try {
                $sessionToken = $this->authService->login($request->validated()['email'], $request->validated()['password'], $request->validated()['keep_session']);

                return response()->json([
                    'session_token' => $sessionToken,
                    'session_expiration' => TokenHelper::calculateSessionExpiration($request->validated()['keep_session'])->toDateTimeString(),
                ], 200);
            } catch (\Exception $e) {
                event(new FailedLogin($request->validated()['email']));
                return response()->json(['message' => $e->getMessage()], 401);
            }
        } else {
            // New code path
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required|min:8', // Keep the password validation rule from the existing code
                'keep_session' => 'sometimes|boolean',
            ], [
                'email.required' => 'Email is required.',
                'email.email' => 'Invalid email format.',
                'password.required' => 'Password is required.',
                'password.min' => 'Password must be at least 8 characters long.', // Keep the password validation message from the existing code
                'keep_session.boolean' => 'Keep session must be a boolean.',
            ]);

            if ($validator->fails()) {
                return ApiResponse::failedLoginResponse($validator->errors()->first()); // Use ApiResponse from the new code
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
                    event(new FailedLogin($credentials['email']));
                    return $this->handleLoginFailure($request); // Pass $request to handleLoginFailure as per new code
                }
            } catch (\Exception $e) {
                event(new FailedLogin($credentials['email']));
                return response()->json(['message' => 'Internal Server Error'], 500);
            }
        }
    }

    public function handleLoginFailure(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ], [
            'email.required' => 'Email is required.',
            'email.email' => 'Invalid email format.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first()
            ], 400);
        }

        return response()->json([
            'status' => 401,
            'message' => 'Login failed. Please check your email and password and try again.'
        ], 401);
    }

    public function cancelLogin(): JsonResponse
    {
        $this->sessionService->cancelLoginProcess();

        event(new \App\Events\LoginCancelledEvent()); // Keep the event from the new code

        return response()->json([
            'message' => 'Login process has been canceled.' // Use the message from the existing code
        ], 200);
    }

    // ... other methods ...
}

// Register the route for handling login failure
Route::get('/api/login/failure', [LoginController::class, 'handleLoginFailure']);
// Register the route for canceling login
Route::post('/api/login/cancel', [LoginController::class, 'cancelLogin']);
// ... other routes ...
