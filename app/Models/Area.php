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
    protected $table = 'areas';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'created_at', // Added from new code
        'updated_at', // Added from new code
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        // Usually, we don't hide any fields in the areas table, but if needed, add them here.
        // No changes needed here as there is no conflict.
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the request areas associated with the area.
     * This method is from the new code and has a different name and potentially a different related model.
     * We need to keep both relationships if they are different.
     */
    public function requestAreas()
    {
        return $this->hasMany(RequestArea::class, 'area_id');
    }

    /**
     * Define the one-to-many relationship with RequestAreaSelection.
     * This method is from the existing code and should be kept as it is.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function requestAreaSelections()
    {
        return $this->hasMany(RequestAreaSelection::class, 'area_id');
    }
}
