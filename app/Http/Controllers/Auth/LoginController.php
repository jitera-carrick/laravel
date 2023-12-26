<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class LoginController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function login(LoginRequest $request): JsonResponse
    {
        // ... existing login method code ...
    }

    public function cancelLogin(Request $request): JsonResponse
    {
        // ... existing cancelLogin method code ...
    }

    // ... other methods ...

    public function editUserProfile(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:users,id',
            'email' => [
                'required',
                'email',
                Rule::unique('users')->ignore($request->id),
            ],
            'password' => 'required|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        try {
            $user = $this->authService->getUserById($request->id);

            if ($request->email !== $user->email && $validator->sometimes('email', 'unique:users,email', function ($input) {
                return $input->email !== $user->email;
            })->validate()) {
                $user->email = $request->email;
            }

            $user->password_hash = Hash::make($request->password);
            $user->save();

            // Update the "updated_at" column with the current timestamp
            $user->touch();

            return response()->json(['message' => 'User profile updated successfully'], 200);
        } catch (\Exception $e) {
            Log::error('User profile update exception: ' . $e->getMessage());

            return response()->json(['message' => 'Failed to update user profile', 'error' => $e->getMessage()], 500);
        }
    }
}
