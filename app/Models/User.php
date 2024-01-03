<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    // ... [rest of the code remains unchanged]

    // Define the one-to-many relationship with MenuItem
    public function menuItems()
    {
        return $this->hasMany(MenuItem::class, 'user_id');
    }

    // Define the one-to-many relationship with PasswordResetRequest
    public function passwordResetRequests()
    {
        return $this->hasMany(PasswordResetRequest::class, 'user_id');
    }

    // Define the one-to-many relationship with StylistRequest
    public function stylistRequests()
    {
        return $this->hasMany(StylistRequest::class, 'user_id');
    }

    // ... [rest of the code remains unchanged]
}
