<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Request extends Model
{
    use HasFactory;

    protected $table = 'requests';

    protected $fillable = [
        'area',
        'menu',
        'hair_concerns',
        'status',
        'user_id',
        // Assuming there are new columns to be added, they should be listed here.
        // 'new_column_name', // Add new column names to the $fillable array
    ];

    protected $hidden = [
        // Existing hidden columns
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        // Assuming there are new columns with date/time to be cast, they should be listed here.
        // 'new_date_column' => 'datetime', // Add new date/time columns to the $casts array
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function requestImages()
    {
        return $this->hasMany(RequestImage::class, 'request_id');
    }

    // Define the relationship with RequestAreaSelections
    public function requestAreaSelections()
    {
        return $this->hasMany(RequestAreaSelection::class, 'request_id');
    }

    // Define the relationship with RequestMenuSelections
    public function requestMenuSelections()
    {
        return $this->hasMany(RequestMenuSelection::class, 'request_id');
    }

    // Assuming there are new relationships to be added, they should be defined here.
    // For example, if there's a new one-to-many relationship with a new table 'request_details':
    /*
    public function requestDetails()
    {
        return $this->hasMany(RequestDetail::class, 'request_id');
    }
    */
}
