
<?php

namespace App\Services;

use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function updateUserProfile(int $userId, string $email, string $passwordHash): bool
    {
        try {
            $user = User::findOrFail($userId);
            $existingUser = User::where('email', $email)->where('id', '<>', $userId)->first();

            if ($existingUser) {
                throw new Exception("Email already in use by another user.");
            }

            if ($user->email !== $email || !Hash::check($passwordHash, $user->password_hash)) {
                $user->email = $email;
                $user->password_hash = Hash::make($passwordHash);
                $user->save();
            }

            return true;
        } catch (Exception $e) {
            // Handle exception or log it
            return false;
        }
    }
}
