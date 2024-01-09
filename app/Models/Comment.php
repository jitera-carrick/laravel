<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'status',
        'user_id',
        // Note: 'created_at' and 'updated_at' are managed by Eloquent's timestamp handling
        // and should not be included in the $fillable array.
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        // If there are any attributes that you want to be hidden, add them here.
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'updated_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    /**
     * Get the user that owns the comment.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Other existing methods...

    // New methods can be added below as needed.
}
