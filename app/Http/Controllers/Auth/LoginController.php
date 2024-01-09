<?php

namespace App\Http\Controllers\Auth;

use App\Events\FailedLogin;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Responses\ApiResponse;
use App\Models\User;
use App\Services\AuthService;
use App\Services\SessionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
    protected $authService;
    protected $sessionService;

    public function __construct(AuthService $authService, SessionService $sessionService)
    {
        $this->authService = $authService;
        $this->sessionService = $sessionService;
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
            'keep_session' => 'sometimes|boolean'
        ], [
            'email.required' => 'Email is required.',
            'email.email' => 'Invalid email format.',
            'password.required' => 'Password is required.',
            'keep_session.boolean' => 'Keep Session must be a boolean.'
        ]);

        if ($validator->fails()) {
            $response = new ApiResponse();
            $response->error = $validator->errors()->first();
            $response->status = 'error';
            $response->code = 400;

            return response()->json($response->toArray(), 400);
        }

        $validated = $validator->validated();

        $user = User::where('email', $validated['email'])->first();

        if (!$user || !$this->authService->verifyPassword($user, $validated['password'])) {
            Event::dispatch(new FailedLogin($validated['email'], now()));

            $response = new ApiResponse();
            $response->error = 'Login failed. Please check your credentials and try again or reset your password.';
            $response->status = 'error';
            $response->code = 401;

            return response()->json($response->toArray(), 401);
        }

        $sessionData = $this->sessionService->createSessionToken($user, $validated['keep_session'] ?? false);
        $user->updateSessionInfo($sessionData['session_token'], $sessionData['session_expiration']);

        return new JsonResponse([
            'status' => 200,
            'message' => 'Login successful.',
            'session_token' => $sessionData['session_token'],
            'session_expiration' => $sessionData['session_expiration']
        ], 200);
    }

    public function handleLoginFailure(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
        ]);

        if (!$validated['email']) {
            $response = new ApiResponse();
            $response->error = 'Email is required.';
            $response->status = 'error';
            $response->code = 400;

            return response()->json($response->toArray(), 400);
        }

        return response()->json([
            'status' => 200,
            'message' => 'Login failed. Please check your credentials and try again.'
        ], 200);
    }
}

// Register the new route for handling login failure
Route::post('/api/users/login_failure', [LoginController::class, 'handleLoginFailure']);
