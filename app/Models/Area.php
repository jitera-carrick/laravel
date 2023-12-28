<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'areas'; // Table name defined

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'request_id', // New column added to fillable from new code
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        // Usually, we don't hide any fields in the areas table, but if needed, add them here.
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime', // Cast the created_at field
        'updated_at' => 'datetime', // Cast the updated_at field
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
     * Define the one-to-many relationship with RequestAreaSelection.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function requestAreaSelections()
    {
        return $this->hasMany(RequestAreaSelection::class, 'area_id'); // Relationship from existing code
    }
}
