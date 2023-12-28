<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'menus'; // Table name confirmed

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'request_id', // Column added to fillable from new code
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        // No fields specified to be hidden currently.
        // Sensitive fields to be added here in the future if necessary.
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        // Additional fields to be cast can be added here as needed.
    ];

    /**
     * Define the many-to-one relationship with Request.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function request()
    {
        return $this->belongsTo(Request::class, 'request_id'); // Relationship added from new code
    }

    /**
     * Define the one-to-many relationship with RequestMenuSelection.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function requestMenuSelections()
    {
        return $this->hasMany(RequestMenuSelection::class, 'menu_id'); // Relationship retained from existing code
    }
}
