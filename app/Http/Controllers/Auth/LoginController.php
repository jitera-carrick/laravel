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
use App\Models\User;

class LoginController extends Controller
{
    protected $sessionService;
    protected $authService;

    public function __construct(SessionService $sessionService, AuthService $authService = null)
    {
        $this->sessionService = $sessionService;
        $this->authService = $authService ?: new AuthService();
    }
    
    public function login(Request $request): JsonResponse
    {
        // Use LoginRequest if available, otherwise fallback to manual validation
        if ($request instanceof LoginRequest) {
            $credentials = $request->validated();
        } else {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required|min:8',
                'keep_session' => 'sometimes|boolean',
            ], [
                'email.required' => 'Email is required.',
                'email.email' => 'Invalid email format.',
                'password.required' => 'Password is required.',
                'password.min' => 'Password must be at least 8 characters long.',
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
            $sessionData = $this->authService->attemptLogin($credentials['email'], $credentials['password'], $keepSession);

            if ($sessionData) {
                $user = User::where('email', $credentials['email'])->first();
                // Use SessionResource if available, otherwise fallback to manual response
                if (isset($sessionData['token'])) {
                    return response()->json([
                        'status' => 200,
                        'message' => 'Login successful.',
                        'session_token' => $sessionData['token'],
                        'session_expiration' => $sessionData['expiration'],
                    ], 200);
                } elseif (isset($sessionData->session_token)) {
                    return response()->json([
                        'status' => 200,
                        'session_token' => $sessionData->session_token,
                        'session_expiration' => $sessionData->session_expiration,
                        'user' => [
                            'id' => $user->id,
                            'name' => $user->name,
                            'email' => $user->email,
                        ]
                    ], 200);
                } else {
                    return new SessionResource($sessionData);
                }
            } else {
                event(new FailedLogin($credentials['email']));
                return $this->handleLoginFailure($request);
            }
        } catch (\Exception $e) {
            return ApiResponse::errorResponse($e->getMessage());
        }
    }

    public function handleLoginFailure(Request $request): JsonResponse
    {
        $email = $request->input('email', null);
        if ($email) {
            event(new FailedLogin($email));
            LoginAttempt::create([
                'email' => $email,
                'attempted_at' => now(),
                'successful' => false,
            ]);
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
            // Merged the response from the new code and existing code
            $response = [
                'status' => 200,
                'message' => 'Login process canceled successfully.'
            ];
            // Check if ApiResponse::loginCanceled() exists and use it if available
            if (method_exists(ApiResponse::class, 'loginCanceled')) {
                $response = ApiResponse::loginCanceled();
            }
            return response()->json($response, 200);
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

// Register the route for login
Route::post('/api/login', [LoginController::class, 'login']);
