
<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use App\Helpers\TokenHelper;
use App\Http\Resources\SessionResource; // Assuming SessionResource is imported

class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $email = $request->validated()['email']; // Changed from $request->input('email') to $request->validated()['email']
            $password = $request->input('password');
            $keepSession = $request->input('keep_session');

            $sessionToken = $this->authService->login($email, $password, $keepSession);

            if (!$sessionToken) {
                return response()->json(['message' => 'Invalid credentials.'], 401);
            }

            // Changed from returning a JsonResponse to returning a SessionResource
            return new SessionResource($sessionToken);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred during login.'], 500);
        }
    }
}
