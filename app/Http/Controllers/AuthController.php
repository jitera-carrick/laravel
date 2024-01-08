<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\ErrorResource;
use App\Http\Resources\SuccessResource;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->middleware('guest')->except('logout');
        $this->authService = $authService;
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            $errorMessages = [];

            if ($errors->has('email')) {
                $errorMessages['email'] = "Please enter a valid email address.";
            }

            if ($errors->has('password')) {
                $errorMessages['password'] = "Password cannot be empty.";
            }

            return (new ErrorResource([
                'message' => $errorMessages,
                'status_code' => 422
            ]))->response()->setStatusCode(422);
        }

        $credentials = $request->validated();
        $user = $this->authService->findUserByEmail($credentials['email']);

        if ($user && Hash::check($credentials['password'], $user->password_hash)) {
            $sessionToken = $this->authService->generateSessionToken($user);
            return (new SuccessResource([
                'session_token' => $sessionToken,
                'message' => 'Login successful.'
            ]))->response()->setStatusCode(200);
        } else {
            return (new ErrorResource([
                'message' => 'Invalid email or password.',
                'status_code' => 401
            ]))->response()->setStatusCode(401);
        }
    }
}
