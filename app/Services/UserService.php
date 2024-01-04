
<?php

namespace App\Services;

use App\Models\User;
use App\Models\EmailVerificationToken;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserService
{
    public function createUser($userData)
    {
        // Check for uniqueness of email and username
        if (User::where('email', $userData['email'])->exists() ||
            User::where('username', $userData['username'])->exists()) {
            throw new \Exception('Email or username already exists.');
        }

        // Hash the password with a generated salt
        $salt = Str::random(16);
        $hashedPassword = Hash::make($userData['password'] . $salt);

        // Create a new User model instance
        $user = new User([
            'name' => $userData['name'],
            'email' => $userData['email'],
            'username' => $userData['username'],
            'password_hash' => $hashedPassword,
            'password_salt' => $salt,
        ]);

        // Save the user to the database
        $user->save();

        // Generate an email verification token and associate it with the user
        $emailVerificationToken = new EmailVerificationToken([
            'token' => Str::random(60),
            'expires_at' => now()->addHours(24),
            'user_id' => $user->id,
        ]);
        $emailVerificationToken->save();

        return $user;
    }
}
