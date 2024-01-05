<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailVerificationToken extends Model
{
    use HasFactory;

    protected $table = 'email_verification_tokens';

    protected $fillable = [
        'token',
        'expires_at',
        'verified', // This is the new field from the new code
        'user_id',
    ];

    protected $hidden = [
        // If there are any columns that should be hidden from array outputs, they should be listed here.
        // No hidden columns are specified in both new and existing code.
    ];

    protected $casts = [
        'created_at' => 'datetime', // Added from new code
        'updated_at' => 'datetime', // Added from new code
        'expires_at' => 'datetime',
        'verified'   => 'boolean', // This cast is updated from 'used' to 'verified' to reflect the new code
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // If there are any additional methods or relationships, they should be defined here.
    // No additional methods or relationships are specified in both new and existing code.
}
