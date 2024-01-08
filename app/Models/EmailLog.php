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
     * Log an email sending action.
     *
     * @param string $email_type
     * @param int $user_id
     * @return void
     */
    public function logEmailAction($email_type, $user_id)
    {
        $this->create(['email_type' => $email_type, 'sent_at' => now(), 'user_id' => $user_id]);
    }

    /**
     * Log an email sending action.
     *
     * @param string $email
     * @param string $email_type
     * @return void
    public function logEmailSent($email, $email_type = 'reset_password')
    {
        $this->create(['email_type' => $email_type, 'sent_at' => now(), 'user_id' => User::where('email', $email)->value('id')]);
    }

    /**
     * Create a new log entry for registration emails.
     *
     * @param string $email
     * @param int $userId
     * @return void
     */
    public static function logRegistrationEmail($email, $userId)
    {
        self::create(['email_type' => 'registration', 'sent_at' => now(), 'user_id' => $userId]);
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
