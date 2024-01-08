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
        'priority',
        'created_at',
        'updated_at',
        'new_column_1',
        'new_column_2',
        'user_id',
    ];

    protected $hidden = [
        // Existing hidden columns
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        // Add new date/time columns to the $casts array if needed
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Assuming RequestArea, RequestMenu, RequestAreaSelection, and RequestImage models exist and are related to the Request model
    public function requestAreas()
    {
        return $this->hasMany(RequestArea::class, 'request_id');
    }

    public function requestMenus()
    {
        return $this->hasMany(RequestMenu::class, 'request_id');
    }

    public function requestAreaSelections()
    {
        return $this->hasMany(RequestAreaSelection::class, 'request_id');
    }

    public function requestImages()
    {
        return $this->hasMany(RequestImage::class, 'request_id');
    }

    // Assuming the User model has a one-to-many relationship with Request
    public function users()
    {
        return $this->hasMany(User::class, 'request_id');
    }
}
