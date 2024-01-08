<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\ErrorResource;
use App\Http\Resources\SuccessResource;
use Illuminate\Support\Facades\Hash;
use ReCaptcha\ReCaptcha; // Assuming ReCaptcha is a valid class that exists in the project

class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();

        // Validate reCAPTCHA
        $recaptcha = new ReCaptcha('your-secret-key'); // Replace 'your-secret-key' with the actual secret key
        $resp = $recaptcha->verify($credentials['recaptcha']);
        if (!$resp->isSuccess()) {
            return (new ErrorResource([
                'message' => 'Invalid recaptcha.',
                'status_code' => 401,
            ]))->response()->setStatusCode(401);
        }

        $user = $this->authService->findUserByEmail($credentials['email']);

        if ($user && Hash::check($credentials['password'], $user->password_hash)) {
            $token = $this->authService->generateSessionToken($user);
            return (new SuccessResource([
                'message' => 'Login successful.',
                'access_token' => $token,
            ]))->response();
        }

        return (new ErrorResource([
            'message' => 'Invalid email or password.',
            'status_code' => 401,
        ]))->response()->setStatusCode(401);
    }
}
