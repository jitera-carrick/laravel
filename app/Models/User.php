<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

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
        'email_verified_at', // Kept from existing code
        'remember_token',    // Kept from existing code
        'created_at',        // Kept from existing code
        'updated_at',        // Kept from existing code
        'is_logged_in',      // Added new fillable attribute from new code
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        // 'password' => 'hashed', // Removed because Laravel does not support 'hashed' cast and password is hashed by default
        'is_logged_in' => 'boolean', // Added cast for new attribute from new code
    ];

    // Relationships

    /**
     * Get the stylist associated with the user.
     */
    public function stylist()
    {
        return $this->hasOne(Stylist::class, 'user_id');
    }

    /**
     * Get the messages for the user.
     */
    public function messages()
    {
        return $this->hasMany(Message::class, 'user_id');
    }

    /**
     * Get the treatment plans for the user.
     */
    public function treatmentPlans()
    {
        return $this->hasMany(TreatmentPlan::class, 'user_id');
    }

    /**
     * Get the requests for the user.
     */
    public function requests()
    {
        // This relationship was missing in the existing code and has been added from the new code
        return $this->hasMany(Request::class, 'user_id');
    }
}
