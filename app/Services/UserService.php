<?php

namespace App\Services;

use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;

class UserService
{
    public function updateUserProfile(int $userId, array $data): array
    {
        $validator = Validator::make($data, [
            'email' => 'sometimes|required|email|unique:users,email,' . $userId,
            'password' => 'sometimes|required|min:8',
        ]);

        if ($validator->fails()) {
            return [
                'status' => 422,
                'message' => $validator->errors()->first()
            ];
        }

        try {
            $user = User::findOrFail($userId);

            if (isset($data['email']) && $user->email !== $data['email']) {
                $user->email = $data['email'];
            }

            if (isset($data['password'])) {
                $user->password_hash = Hash::make($data['password']);
            }

            $user->save();

            return [
                'status' => 200,
                'message' => 'Profile updated successfully.'
            ];
        } catch (Exception $e) {
            return [
                'status' => 500,
                'message' => 'An unexpected error occurred on the server.'
            ];
        }
    }

    // Keep the existing methods as they are
    public function updateUser($userId, array $data)
    {
        try {
            $user = User::findOrFail($userId);
            $user->fill($data);
            $user->save();
            return true;
        } catch (Exception $e) {
            // Handle exception or log it
            return false;
        }
    }
}
