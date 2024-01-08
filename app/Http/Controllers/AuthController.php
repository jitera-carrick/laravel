<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Services\AuthService;
use App\Services\RecaptchaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    protected $authService;
    protected $recaptchaService;

    public function __construct(AuthService $authService, RecaptchaService $recaptchaService)
    {
        $this->authService = $authService;
        $this->recaptchaService = $recaptchaService;
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
            'recaptcha' => 'required'
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            $errorMessages = [];

            if ($errors->has('email')) {
                $errorMessages['email'] = 'Invalid email address.';
            }

            if ($errors->has('password')) {
                $errorMessages['password'] = 'Invalid password.';
            }

            if ($errors->has('recaptcha')) {
                $errorMessages['recaptcha'] = 'Invalid recaptcha.';
            }

            return response()->json(['errors' => $errorMessages], 400);
        }

        if (!$this->recaptchaService->verify($request->recaptcha)) {
            return response()->json(['error' => 'Invalid recaptcha'], 422);
        }

        $token = $this->authService->attemptLogin($request->email, $request->password);

        if (!$token) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return response()->json([
            'status' => 200,
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => 3600
        ], 200);
    }
}
