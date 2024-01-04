<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PasswordPolicy extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'password_policies';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'minimum_length',
        'require_uppercase',
        'require_lowercase',
        'require_numbers',
        'require_special_characters',
        'exclude_user_info',
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
        // Typically, password policy fields do not need to be hidden
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'require_uppercase' => 'boolean',
        'require_lowercase' => 'boolean',
        'require_numbers' => 'boolean',
        'require_special_characters' => 'boolean',
        'exclude_user_info' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user that owns the password policy.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
