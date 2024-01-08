
<?php

namespace App\Services;

use App\Models\User;
use App\Models\EmailVerificationToken;
use Illuminate\Support\Facades\Hash;

class UserService
{
    /**
     * Create a new user with the provided details.
     *
     * @param array $userData
     * @return User
     */
    public function createUser(array $userData)
    {
        $userData['password_hash'] = Hash::make($userData['password']);
        $userData['password_salt'] = ''; // Assuming password_salt is handled elsewhere or not used.
        $user = User::create($userData);
        return $user;
    }

    /**
     * Generate an email verification token for the given user.
     *
     * @param User $user
     * @return EmailVerificationToken
     */
    public function generateEmailVerificationToken(User $user)
    {
        return $user->generateEmailConfirmationToken();
    }
}
