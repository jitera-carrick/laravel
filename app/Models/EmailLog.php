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
     * Log the action of sending an email.
     *
     * @param int $userId
     * @param string $emailAddress
     * @param string $emailType
     * @param \DateTime $timestamp
     * @return void
     */
    public function logEmailAction($userId, $emailAddress, $emailType, $timestamp)
    {
        // The new code introduces an additional parameter $emailAddress which is not used in the method.
        // Since the method signature has changed, we need to ensure that calls to this method are updated accordingly.
        // However, to maintain backward compatibility, we can check the number of arguments passed and handle it accordingly.
        $args = func_get_args();
        if (count($args) === 4) {
            // New method signature with 4 parameters
            [$userId, $emailAddress, $emailType, $timestamp] = $args;
        } elseif (count($args) === 3) {
            // Old method signature with 3 parameters
            [$emailType, $userId, $timestamp] = $args;
            $timestamp = now(); // Assuming that the old code always used the current time
        }
        $this->create(['user_id' => $userId, 'email_type' => $emailType, 'sent_at' => $timestamp]);
    }

    /**
     * Log an email sending action.
     *
     * @param string $email
     * @param string $email_type
     * @return void
     */
    public function logEmailSent($email, $email_type = 'reset_password')
    {
        // This method is identical in both the new and existing code, so no changes are needed.
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
        // This method is identical in both the new and existing code, so no changes are needed.
        self::create(['email_type' => 'registration', 'sent_at' => now(), 'user_id' => $userId]);
    }

    /**
     * Relationship with User model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        // This method is identical in both the new and existing code, so no changes are needed.
        return $this->belongsTo(User::class, 'user_id');
    }
}
