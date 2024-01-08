<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Services\AuthService;
use App\Http\Resources\ErrorResource;
use App\Http\Resources\SuccessResource;
use Illuminate\Support\Facades\Validator;
use ReCaptcha\ReCaptcha;

class AuthController extends Controller
{
    public function login(LoginRequest $request)
    {
        // Validate the recaptcha field
        $validator = Validator::make($request->all(), [
            'recaptcha' => 'required|string',
        ]);

        if ($validator->fails()) {
            return new ErrorResource(['error' => 'Invalid recaptcha.'], 422);
        }

        // Verify recaptcha with Google's service
        $recaptcha = new ReCaptcha('your-secret-key'); // Replace 'your-secret-key' with the actual secret key
        $resp = $recaptcha->verify($request->input('recaptcha'), $request->ip());

        if (!$resp->isSuccess()) {
            return new ErrorResource(['error' => 'Invalid recaptcha.'], 401);
        }

        $authService = new AuthService();
        $authenticated = $authService->attempt($request->validated());

        if ($authenticated) {
            return new SuccessResource($authenticated);
        }

        return new ErrorResource(['error' => 'Invalid email or password.'], 401);
    }
}
