<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'messages';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'content',
        'sent_at',
        'user_id',
        'read', // Added new fillable attribute from new code
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'sent_at' => 'datetime',
        'read' => 'boolean', // Added cast for new attribute from new code
    ];

    /**
     * Get the user that sent the message.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Assuming that the "One many users.message_id" relation is a typo and should be "One to many" from users to messages
    // No additional code needed for this relationship since it's already defined in the User model
}
