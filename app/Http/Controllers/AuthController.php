
<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest; // Import the LoginRequest
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Handle the login request.
     *
     * @param LoginRequest $request
     * @return JsonResponse
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
            'recaptcha' => 'required' // Assuming there is a custom validation rule for recaptcha
        ], [
            'email.required' => 'Please enter a valid email address.',
            'email.email' => 'Please enter a valid email address.',
            'password.required' => 'Password cannot be blank.',
            'recaptcha.required' => 'Invalid recaptcha.'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {
            $credentials = $request->only('email', 'password');
            $recaptcha = $request->input('recaptcha');

            $authResult = $this->authService->authenticateUser($credentials['email'], $credentials['password'], $recaptcha);

            if ($authResult['status'] === 'success') {
                return response()->json([
                    'status' => 200,
                    'message' => 'Login successful.',
                    'data' => [
                        'token' => $authResult['session_token'],
                        'user' => $authResult['user']
                    ]
                ], 200);
            }

            return response()->json([
                'status' => 401,
                'message' => $authResult['message']
            ], 401);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'An error occurred during login.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle the logout process for a user.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        // Validate the session token
        $validator = Validator::make($request->all(), [
            'session_token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        // Retrieve the session token from the request
        $sessionToken = $request->input('session_token');

        try {
            // Find the user by session token
            $user = $this->authService->getUserBySessionToken($sessionToken);

            // If user not found, return unauthorized response
            if (!$user) {
                $this->authService->logoutUser($user);

                // Return successful logout response
                return response()->json(['status' => 200, 'message' => 'You have been logged out successfully.'], 200);
            }

            // Invalidate the user session
            $this->authService->invalidateSession($user);

            // Return successful logout response
            return response()->json(['status' => 200, 'message' => 'You have been logged out successfully.'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred during the logout process.'], 500);
        }
    }

    // ... other methods that might exist in the controller ...
}
