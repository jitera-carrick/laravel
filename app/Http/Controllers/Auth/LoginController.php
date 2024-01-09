<?php

namespace App\Http\Controllers\Auth;

use App\Events\FailedLogin;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Responses\ApiResponse;
use App\Models\LoginAttempt;
use App\Models\User;
use App\Services\AuthService;
use App\Services\SessionService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Event;

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
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            // Assuming 'keep_session' is a boolean, add validation if needed
        ]);

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

        return response()->json(['session_token' => $user->session_token]);
    }
}
