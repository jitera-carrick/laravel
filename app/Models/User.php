<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'password_hash',
        'password_salt',
        'last_password_reset',
        'remember_token',
        'created_at',
        'updated_at',
        'is_logged_in',
        'session_token',
        'session_expiration',
        'user_type',
        'stylist_request_id', // New column added to fillable
        'hair_stylist_request_id', // New column added to fillable
    ];

    // Existing relationships...

    // Define the one-to-many relationship with StylistRequests
    public function stylistRequests()
    {
        return $this->hasMany(StylistRequest::class, 'stylist_request_id');
    }

    // Define the one-to-many relationship with HairStylistRequests
    public function hairStylistRequests()
    {
        return $this->hasMany(HairStylistRequest::class, 'hair_stylist_request_id');
    }

    // Other existing relationships...

    // New relationships can be added below as needed.
}
