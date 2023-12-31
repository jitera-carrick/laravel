<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StylistRequest extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'stylist_requests';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'preferred_date',
        'preferred_time',
        'stylist_preferences',
        'status',
        'user_id',
        'stylist_id',
        // 'updated_at' is not needed in $fillable as it is automatically managed by Eloquent
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        // If there are any attributes that should be hidden, list them here.
        // Usually, sensitive fields like passwords are hidden. Add any fields that need to be hidden here.
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'preferred_date' => 'date',
        'preferred_time' => 'datetime', // Corrected the cast type for preferred_time
        'stylist_preferences' => 'array', // Assuming stylist_preferences is a JSON field that should be cast to an array.
        // 'date_time' => 'datetime', // Removed as it seems to be replaced by preferred_date and preferred_time
        // Add other casts here if necessary.
    ];

    /**
     * Get the user that made the stylist request.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // The relationship method name should be plural as it represents a one-to-many relationship
    // This seems to be a mistake in the existing code, as a stylist request likely belongs to a single user.
    // If a one-to-many relationship is needed, it should be properly named and defined.
    // For now, we'll comment it out until further clarification is provided.
    // public function users()
    // {
    //     return $this->hasMany(User::class, 'stylist_request_id');
    // }

    // Other relationships can be added below as needed.
}
