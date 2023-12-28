<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateUserProfileRequest;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    private UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function updateUserProfile(UpdateUserProfileRequest $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 400);
        }

        try {
            $user = $this->userService->findUserById($request->user()->id);

            if ($request->email !== $user->email && !$this->userService->isEmailUnique($request->email, $user->id)) {
                return response()->json(['message' => 'Email already in use by another account.'], 400);
            }

            $user->name = $request->name;
            $user->email = $request->email;
            $user->updated_at = now();
            $user->save();

            return response()->json([
                'status' => 200,
                'message' => 'Profile updated successfully.',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'updated_at' => $user->updated_at->toIso8601String(),
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to update user profile.'], 422);
        }
    }
}
