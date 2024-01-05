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
        'verified',
        'user_id',
    ];

    protected $hidden = [
        // If there are any columns that should be hidden from array outputs, they should be listed here.
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'expires_at' => 'datetime',
        'verified'   => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // If there are any additional methods or relationships, they should be defined here.
}
