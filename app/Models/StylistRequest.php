<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

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
        'date_time',
        'status',
        'user_id',
        'stylist_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        // Usually, sensitive fields like passwords are hidden. Add any fields that need to be hidden here.
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date_time' => 'datetime',
        // Add other casts here if necessary
    ];

    /**
     * Get the user that owns the stylist request.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // The relationship method name should be plural as it represents a one-to-many relationship
    public function users()
    {
        return $this->hasMany(User::class, 'stylist_request_id');
    }
}
