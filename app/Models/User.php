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
        'created_at',
        'updated_at',
        // Other existing fillable attributes...
    ];

    // Other existing methods and relationships...

    /**
     * Get the stylist requests for the user.
     */
    public function stylistRequests()
    {
        return $this->hasMany(StylistRequest::class, 'user_id');
    }

    // New relationships can be added below as needed.
}
