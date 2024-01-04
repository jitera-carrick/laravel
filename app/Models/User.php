<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    // ... existing code ...

    // Define the one-to-many relationship with PasswordPolicies
    public function passwordPolicies()
    {
        return $this->hasOne(PasswordPolicy::class, 'user_id');
    }

    // ... rest of the existing code ...
}
