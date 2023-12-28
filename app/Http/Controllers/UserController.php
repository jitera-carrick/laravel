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
            'new_password' => 'required|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 400);
        }

        try {
            $user = $this->userService->findUserById($request->user_id);

            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json(['message' => 'Current password is incorrect.'], 400);
            }

            if ($request->email !== $user->email && !$this->userService->isEmailUnique($request->email)) {
                return response()->json(['message' => 'Email is already in use.'], 400);
            }

            $user->password = Hash::make($request->new_password);
            $user->email = $request->email;
            $user->email_verified_at = null;
            $user->updated_at = now();
            $user->save();

            if ($request->email !== $user->email) {
                $this->userService->sendProfileUpdateConfirmation($user);
            }

            return response()->json(['message' => 'User profile updated successfully.'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to update user profile.'], 422);
        }
    }
}
