<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    use HasFactory;

    // ... (other properties and methods)

    /**
     * Get the user that made the reservation.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
