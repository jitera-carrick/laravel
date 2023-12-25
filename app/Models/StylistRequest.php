<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StylistRequest extends Model
{
    use HasFactory;

    protected $table = 'stylist_requests';

    protected $fillable = [
        'status',
        'user_id',
        'details', // Existing column
        'area', // New column
        'gender', // New column
        'birth_date', // New column
        'display_name', // New column
        'menu', // New column
        'hair_concerns', // New column
    ];

    protected $hidden = [
        // Usually sensitive data like passwords are hidden, add any sensitive fields here
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'birth_date' => 'date', // New cast for birth_date
        // Add other casts here if necessary
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // New relationship
    public function images()
    {
        return $this->hasMany(Image::class, 'stylist_request_id');
    }
}
