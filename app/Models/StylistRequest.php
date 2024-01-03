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
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'request_details',
        'status',
        'user_id',
        'created_at',
        'updated_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        // Usually, sensitive data like passwords are hidden, but since this table doesn't have such data, this can be left empty or omitted.
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        // Add any other necessary casts here, for example, if 'status' is a boolean, you would add 'status' => 'boolean',
    ];

    /**
     * Get the user that made the stylist request.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
