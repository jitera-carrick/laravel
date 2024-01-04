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

    // Define the one-to-many relationship with Session
    public function sessions()
    {
        return $this->hasMany(Session::class, 'user_id');
    }

    // Define the one-to-many relationship with StylistRequest
    public function stylistRequests()
    {
        return $this->hasMany(StylistRequest::class, 'user_id');
    }

    // Define the one-to-many relationship with PasswordResetRequest
    // This relationship was missing in the new code, so we are keeping it from the existing code
    public function passwordResetRequests()
    {
        return $this->hasMany(PasswordResetRequest::class, 'user_id');
    }

    // Define the has-one relationship with PasswordResetTokens
    // This relationship seems to be a duplicate of the one-to-many relationship with PasswordResetToken
    // Since it's a has-one relationship, it should be singular and not plural
    // Also, it should be named differently to avoid confusion with the one-to-many relationship
    public function latestPasswordResetToken()
    {
        return $this->hasOne(PasswordResetToken::class, 'user_id')->latest();
    }
}
