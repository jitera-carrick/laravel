<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Request extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'requests'; // Table name defined

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'area',
        'menu',
        'hair_concerns',
        'status',
        'user_id', // Existing columns
        // Add new columns to fillable here if any
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        // Existing hidden columns
        // Add new columns to hidden here if any
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        // Existing casts
        // Add new columns to casts here if any
    ];

    /**
     * Get the user that owns the request.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the request images for the request.
     */
    public function requestImages()
    {
        return $this->hasMany(RequestImage::class, 'request_id');
    }

    // Define other relationships here if any
}
