
<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function authenticateUser(string $email, string $password)
    {
        $user = User::where('email', $email)->first();
        if ($user && Hash::check($password, $user->password)) {
            $token = $user->createToken('auth_token')->plainTextToken;
            return ['user' => $user, 'token' => $token];
        }
        return false;
    }
}
