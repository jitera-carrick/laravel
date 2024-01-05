<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailLog extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email_type',
        'sent_at',
        'user_id',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'email_logs';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * Log the email sending action.
     *
     * @param int $userId
     * @return void
     */
    public function logEmailSent($userId)
    {
        $this->create([
            'user_id' => $userId,
            'email_type' => 'password_reset',
            'sent_at' => now(),
        ]);
    }

    /**
     * Relationship with User model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
